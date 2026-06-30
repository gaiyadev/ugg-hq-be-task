<?php

namespace Tests\Unit\Services;

use App\DTOs\Resource\ResourceDTO;
use App\Enums\ResourceStatus;
use App\Exceptions\Resource\InvalidStatusTransitionException;
use App\Models\Resource;
use App\Models\User;
use App\Repositories\Interfaces\ResourceRepositoryInterface;
use App\Services\ResourceService;
use App\Services\SignatureService;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ResourceServiceTest extends TestCase
{
    private ResourceService $service;
    private MockInterface $repositoryMock;
    private MockInterface $signatureMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repositoryMock = Mockery::mock(ResourceRepositoryInterface::class);
        $this->signatureMock = Mockery::mock(SignatureService::class);

        $this->service = new ResourceService(
            $this->repositoryMock,
            $this->signatureMock
        );

        Event::fake();
    }

    public function test_submit_transitions_draft_to_pending()
    {
        $user = User::factory()->make(['id' => 1]);
        $resource = new Resource([
            'id' => 10,
            'title' => 'Test',
            'status' => ResourceStatus::Draft->value
        ]);
        $resource->id = 10;

        $this->repositoryMock->shouldReceive('findById')->with(10)->once()->andReturn($resource);
        
        $updatedResource = clone $resource;
        $updatedResource->status = ResourceStatus::Pending->value;
        $updatedResource->updated_by = 1;

        $this->repositoryMock->shouldReceive('update')
            ->once()
            ->with($resource, Mockery::subset(['status' => 'pending', 'updated_by' => 1]))
            ->andReturn($updatedResource);

        $result = $this->service->submit(10, $user);

        $this->assertEquals(ResourceStatus::Pending->value, $result->status);
    }

    public function test_submit_throws_exception_if_already_pending()
    {
        $user = User::factory()->make(['id' => 1]);
        $resource = new Resource([
            'id' => 10,
            'title' => 'Test',
            'status' => ResourceStatus::Pending->value
        ]);
        $resource->id = 10;

        $this->repositoryMock->shouldReceive('findById')->with(10)->once()->andReturn($resource);

        $this->expectException(InvalidStatusTransitionException::class);

        $this->service->submit(10, $user);
    }

    public function test_approve_transitions_pending_to_approved_and_generates_signature()
    {
        $user = User::factory()->make(['id' => 99]);
        $resource = new Resource([
            'id' => 10,
            'title' => 'Test',
            'description' => 'Desc',
            'created_by' => 1,
            'status' => ResourceStatus::Pending->value
        ]);
        $resource->id = 10;

        $this->repositoryMock->shouldReceive('findById')->with(10)->once()->andReturn($resource);

        $this->signatureMock->shouldReceive('signResource')
            ->once()
            ->andReturn('mock-sha-256');

        $updatedResource = clone $resource;
        $updatedResource->status = ResourceStatus::Approved->value;
        
        $this->repositoryMock->shouldReceive('update')
            ->once()
            ->with($resource, Mockery::on(function ($data) {
                return $data['status'] === 'approved' 
                    && $data['signature'] === 'mock-sha-256'
                    && $data['approved_by'] === 99;
            }))
            ->andReturn($updatedResource);

        $result = $this->service->approve(10, $user);

        $this->assertEquals(ResourceStatus::Approved->value, $result->status);
    }
}
