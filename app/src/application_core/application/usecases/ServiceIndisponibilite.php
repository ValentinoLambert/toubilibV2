<?php

namespace toubilib\core\application\usecases;

use DateTimeImmutable;
use Exception;
use Ramsey\Uuid\Uuid;
use toubilib\core\application\dto\IndisponibiliteDTO;
use toubilib\core\application\dto\InputIndisponibiliteDTO;
use toubilib\core\application\exceptions\ResourceNotFoundException;
use toubilib\core\application\exceptions\ValidationException;
use toubilib\core\application\ports\IndisponibiliteRepositoryInterface;
use toubilib\core\application\ports\PraticienRepositoryInterface;
use toubilib\core\application\ports\RdvRepositoryInterface;
use toubilib\core\domain\entities\praticien\Indisponibilite;

class ServiceIndisponibilite implements ServiceIndisponibiliteInterface
{
    private IndisponibiliteRepositoryInterface $repository;
    private PraticienRepositoryInterface $praticienRepository;
    private RdvRepositoryInterface $rdvRepository;

    public function __construct(
        IndisponibiliteRepositoryInterface $repository,
        PraticienRepositoryInterface $praticienRepository,
        RdvRepositoryInterface $rdvRepository
    ) {
        $this->repository = $repository;
        $this->praticienRepository = $praticienRepository;
        $this->rdvRepository = $rdvRepository;
    }

    public function listerIndisponibilites(string $praticienId, string $dateDebut, string $dateFin): array
    {
        $this->assertPraticienExiste($praticienId);

        $debut = $this->parseDate($dateDebut);
        $fin = $this->parseDate($dateFin);
        $this->assertPeriode($debut, $fin);

        $rows = $this->repository->findByPraticienBetween(
            $praticienId,
            $debut->format('Y-m-d H:i:s'),
            $fin->format('Y-m-d H:i:s')
        );

        return array_map(fn(Indisponibilite $indispo) => $this->mapToDto($indispo), $rows);
    }

    public function creerIndisponibilite(InputIndisponibiliteDTO $dto): IndisponibiliteDTO
    {
        $this->assertPraticienExiste($dto->praticienId);
        $debut = $this->parseDate($dto->dateDebut);
        $fin = $this->parseDate($dto->dateFin);
        $this->assertPeriode($debut, $fin);

        $motif = $dto->motif ? trim($dto->motif) : null;

        $rdvs = $this->rdvRepository->findOverlapping(
            $dto->praticienId,
            $debut->format('Y-m-d H:i:s'),
            $fin->format('Y-m-d H:i:s')
        );
        foreach ($rdvs as $rdv) {
            if (!$rdv->isCancelled()) {
                throw new ValidationException('Un rendez-vous est déjà planifié sur cette période.');
            }
        }

        $indispos = $this->repository->findOverlapping(
            $dto->praticienId,
            $debut->format('Y-m-d H:i:s'),
            $fin->format('Y-m-d H:i:s')
        );
        if (count($indispos) > 0) {
            throw new ValidationException('Une indisponibilité est déjà déclarée sur cette période.');
        }

        $entity = new Indisponibilite(
            Uuid::uuid4()->toString(),
            $dto->praticienId,
            $debut->format('Y-m-d H:i:s'),
            $fin->format('Y-m-d H:i:s'),
            $motif
        );

        $this->repository->save($entity);

        return $this->mapToDto($entity);
    }

    public function supprimerIndisponibilite(string $id, ?string $praticienId = null): void
    {
        if (!Uuid::isValid($id)) {
            throw new ValidationException('Identifiant d\'indisponibilité invalide.');
        }

        $indispo = $this->repository->findById($id);
        if ($indispo === null) {
            throw new ResourceNotFoundException(sprintf('Indisponibilité %s introuvable', $id));
        }

        if ($praticienId !== null && $indispo->praticien_id !== $praticienId) {
            throw new ValidationException('L\'indisponibilité ne correspond pas à ce praticien.');
        }

        $this->repository->delete($id);
    }

    private function assertPraticienExiste(string $id): void
    {
        $praticien = $this->praticienRepository->findDetailById($id);
        if ($praticien === null) {
            throw new ResourceNotFoundException(sprintf('Praticien %s introuvable', $id));
        }
    }

    private function parseDate(string $value): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($value);
        } catch (Exception $exception) {
            throw new ValidationException(sprintf('Date invalide : %s', $value), previous: $exception);
        }
    }

    private function assertPeriode(DateTimeImmutable $debut, DateTimeImmutable $fin): void
    {
        if ($fin <= $debut) {
            throw new ValidationException('La date de fin doit être postérieure à la date de début.');
        }
    }

    private function mapToDto(Indisponibilite $indispo): IndisponibiliteDTO
    {
        return new IndisponibiliteDTO(
            $indispo->id,
            $indispo->praticien_id,
            $indispo->date_debut,
            $indispo->date_fin,
            $indispo->motif
        );
    }
}
