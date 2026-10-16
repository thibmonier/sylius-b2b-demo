<?php

declare(strict_types=1);

namespace App\Demo\Mcp;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusB2bMcpPlugin\Account\AccountUsability;
use Tmonier\SyliusB2bMcpPlugin\Account\AccountUsabilityCheckerInterface;
use Tmonier\SyliusRegistrationApprovalPlugin\Approval\ApprovalPolicyInterface;
use Tmonier\SyliusRegistrationApprovalPlugin\Approval\ApprovalStatus;
use Tmonier\SyliusRegistrationApprovalPlugin\Entity\Customer\ApprovalAwareInterface;

/**
 * US-020 — An AI assistant has the access of the customer it is connected to: a customer whose registration is pending
 * or rejected may neither connect an assistant nor use one, with the tokens he got before. The rule only exists where
 * registrations are validated (the channels of the approval policy); the customer is the only input, as the extension
 * point of the MCP plugin wants it.
 */
final readonly class ApprovedRegistrationChecker implements AccountUsabilityCheckerInterface
{
    /** @param RepositoryInterface<ChannelInterface> $channelRepository */
    public function __construct(
        private ApprovalPolicyInterface $policy,
        #[Autowire(service: 'sylius.repository.channel')]
        private RepositoryInterface $channelRepository,
    ) {
    }

    public function check(CustomerInterface $customer): AccountUsability
    {
        if (!$customer instanceof ApprovalAwareInterface || ApprovalStatus::Approved === $customer->getApprovalStatus()) {
            return AccountUsability::usable();
        }

        foreach ($this->channelRepository->findAll() as $channel) {
            if ($this->policy->requiresApproval($channel)) {
                return AccountUsability::refused('registration_' . $customer->getApprovalStatus()->value);
            }
        }

        return AccountUsability::usable();
    }
}
