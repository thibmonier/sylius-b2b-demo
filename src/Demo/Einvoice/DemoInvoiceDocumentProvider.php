<?php

declare(strict_types=1);

namespace App\Demo\Einvoice;

use Sylius\InvoicingPlugin\Doctrine\ORM\InvoiceRepositoryInterface;
use Sylius\InvoicingPlugin\Entity\InvoiceInterface;
use Sylius\InvoicingPlugin\Provider\InvoiceFileProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusEinvoiceTransmissionPlugin\Transmission\InvoiceDocument;
use Tmonier\SyliusEinvoiceTransmissionPlugin\Transmission\InvoiceDocumentProviderInterface;
use Tmonier\SyliusFacturXPlugin\Generator\FacturXGeneratorInterface;

/** US-184: gives back the invoice for a relaunch: its Factur-X XML (regenerated) and the PDF the Invoicing plugin stored. */
final readonly class DemoInvoiceDocumentProvider implements InvoiceDocumentProviderInterface
{
    public function __construct(
        #[Autowire(service: 'sylius_invoicing.repository.invoice')]
        private InvoiceRepositoryInterface $invoices,
        private InvoiceFileProviderInterface $files,
        private FacturXGeneratorInterface $facturX,
    ) {
    }

    public function provide(string $invoiceId): InvoiceDocument
    {
        $invoice = $this->invoices->find($invoiceId);
        if (!$invoice instanceof InvoiceInterface) {
            throw new \RuntimeException(sprintf('The invoice "%s" does not exist.', $invoiceId));
        }

        $pdf = $this->files->provide($invoice);

        return new InvoiceDocument($invoiceId, $invoice->number(), $this->facturX->generateXml($invoice), $pdf->content(), $pdf->filename());
    }
}
