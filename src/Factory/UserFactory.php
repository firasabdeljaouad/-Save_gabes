<?php

namespace App\Factory;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityRepository;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Persistence\ProxyRepositoryDecorator;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    #[\Override]    public static function class(): string
    {
        return User::class;
    }

        /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]    protected function defaults(): array|callable    {
        return [
            'email' => 'admin@fac.ma',
            'roles' => ['ROLE_ADMIN'],
            'password' =>'$2y$10$kb5/iPboFQ9VSGipRY8uUuXFhqLjeO7F/s7uc2X8tOEIEqRhcIP1u',
            //'username' => 'Admin',
            'firstName' => 'Admin',
            'lastName' => 'Admin',
            'phoneNumber' => '0000000000',
            'status' => 'active',
            'createdAt' => new \DateTime(),   // <- ajouté
            'updatedAt' => new \DateTime(),   // <- ajouté
        ];

    }

        /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(User $user): void {})
        ;
    }
}
