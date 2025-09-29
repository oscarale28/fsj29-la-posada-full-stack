<?php

declare(strict_types=1);

namespace App\Shared\Repositories;

/**
 * Base Repository Interface - Defines common repository operations
 */
interface RepositoryInterface
{
    /**
     * Find entity by ID
     *
     * @param int $id
     * @return mixed|null
     */
    public function findById(int $id): mixed;

    /**
     * Find all entities
     *
     * @return array
     */
    public function findAll(): array;

    /**
     * Save entity (create or update)
     *
     * @param mixed $entity
     * @return mixed
     */
    public function save(mixed $entity): mixed;

    /**
     * Delete entity by ID
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Check if entity exists by ID
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool;
}
