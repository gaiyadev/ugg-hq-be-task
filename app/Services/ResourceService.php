<?php

namespace App\Services;

use App\Enums\ResourceStatus;
use App\Exceptions\Resource\InvalidStatusTransitionException;
use App\Exceptions\Resource\ResourceNotFoundException;
use App\DTOs\Resource\ResourceDTO;
use App\Events\Resource\ResourceCreated;
use App\Events\Resource\ResourceDeleted;
use App\Events\Resource\ResourceUpdated;
use App\Models\Resource;
use App\Models\User;
use App\Repositories\Interfaces\ResourceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ResourceService
{
    public function __construct(
        private readonly ResourceRepositoryInterface $resourceRepository,
        private readonly SignatureService $signatureService,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $perPage, array $filters = []): LengthAwarePaginator
    {
        return $this->resourceRepository->paginate($perPage, $filters);
    }

    public function findOrFail(string $id): Resource
    {
        $resource = $this->resourceRepository->findById($id);

        if (!$resource) {
            throw new ResourceNotFoundException($id);
        }

        return $resource;
    }

    /**
     * Create a new resource.
     * Sets created_by and dispatches ResourceCreated event for audit logging.
     */
    public function create(ResourceDTO $dto, User $creator): Resource
    {
        return DB::transaction(function () use ($dto, $creator) {
            $data = array_merge($dto->toArray(), [
                'created_by' => $creator->id,
                'updated_by' => $creator->id,
            ]);

            $resource = $this->resourceRepository->create($data);

            event(new ResourceCreated($resource, $creator));

            return $resource;
        });
    }

    /**
     * Update a resource.
     * Only draft/pending resources can be updated.
     * Dispatches ResourceUpdated event for audit logging.
     */
    public function update(string $id, ResourceDTO $dto, User $updater): Resource
    {
        return DB::transaction(function () use ($id, $dto, $updater) {
            $resource = $this->findOrFail($id);

            $data = array_merge($dto->toArray(), [
                'updated_by' => $updater->id,
                // If status is changing, validate state machine
                'status'     => $dto->status->value,
            ]);

            // If status is changing, enforce the state machine
            $currentStatus = ResourceStatus::from($resource->status);
            if ($currentStatus !== $dto->status && !$currentStatus->canTransitionTo($dto->status)) {
                throw new InvalidStatusTransitionException($currentStatus, $dto->status);
            }

            $updated = $this->resourceRepository->update($resource, $data);

            event(new ResourceUpdated($updated, $updater));

            return $updated;
        });
    }

    /**
     * Approve a resource.
     * Generates SHA256 signature, transitions status to approved.
     */
    public function approve(string $id, User $approver): Resource
    {
        return DB::transaction(function () use ($id, $approver) {
            $resource      = $this->findOrFail($id);
            $currentStatus = ResourceStatus::from($resource->status);

            if (!$currentStatus->canTransitionTo(ResourceStatus::Approved)) {
                throw new InvalidStatusTransitionException($currentStatus, ResourceStatus::Approved);
            }

            $approvedAt = now();
            $signature  = $this->signatureService->signResource(
                resourceId:  $resource->id,
                title:       $resource->title,
                description: $resource->description,
                createdBy:   $resource->created_by,
                approvedAt:  $approvedAt->toIso8601String(),
            );

            $updated = $this->resourceRepository->update($resource, [
                'status'      => ResourceStatus::Approved->value,
                'approved_by' => $approver->id,
                'approved_at' => $approvedAt,
                'signature'   => $signature,
                'updated_by'  => $approver->id,
            ]);

            event(new ResourceUpdated($updated, $approver));

            return $updated;
        });
    }

    /**
     * Reject a resource (pending → rejected).
     */
    public function reject(string $id, User $rejector): Resource
    {
        return DB::transaction(function () use ($id, $rejector) {
            $resource      = $this->findOrFail($id);
            $currentStatus = ResourceStatus::from($resource->status);

            if (!$currentStatus->canTransitionTo(ResourceStatus::Rejected)) {
                throw new InvalidStatusTransitionException($currentStatus, ResourceStatus::Rejected);
            }

            $updated = $this->resourceRepository->update($resource, [
                'status'     => ResourceStatus::Rejected->value,
                'updated_by' => $rejector->id,
            ]);

            event(new ResourceUpdated($updated, $rejector));

            return $updated;
        });
    }

    /**
     * Submit a draft resource for review (draft → pending).
     */
    public function submit(string $id, User $submitter): Resource
    {
        return DB::transaction(function () use ($id, $submitter) {
            $resource      = $this->findOrFail($id);
            $currentStatus = ResourceStatus::from($resource->status);

            if (!$currentStatus->canTransitionTo(ResourceStatus::Pending)) {
                throw new InvalidStatusTransitionException($currentStatus, ResourceStatus::Pending);
            }

            $updated = $this->resourceRepository->update($resource, [
                'status'     => ResourceStatus::Pending->value,
                'updated_by' => $submitter->id,
            ]);

            event(new ResourceUpdated($updated, $submitter));

            return $updated;
        });
    }

    /**
     * Soft-delete a resource. Dispatches ResourceDeleted event.
     */
    public function delete(string $id, User $actor): bool
    {
        $resource = $this->findOrFail($id);

        $result = $this->resourceRepository->delete($resource);

        if ($result) {
            event(new ResourceDeleted($resource, $actor));
        }

        return $result;
    }

    /**
     * Verify a resource's SHA256 signature for tamper detection.
     */
    public function verifySignature(string $id): bool
    {
        $resource = $this->findOrFail($id);

        if (!$resource->signature || !$resource->approved_at) {
            return false;
        }

        return $this->signatureService->verifyResource(
            storedSignature: $resource->signature,
            resourceId:      $resource->id,
            title:           $resource->title,
            description:     $resource->description,
            createdBy:       $resource->created_by,
            approvedAt:      $resource->approved_at->toIso8601String(),
        );
    }
}
