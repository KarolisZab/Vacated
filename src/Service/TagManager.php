<?php

namespace App\Service;

use App\DTO\TagDTO;
use App\Entity\Tag;
use App\Exception\ValidationFailureException;
use App\Trait\LoggerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TagManager
{
    use LoggerTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ) {
    }

    public function createOrGetTag(TagDTO $tagDTO, bool $flush = true): Tag
    {
        try {
            $existingTag = $this->entityManager->getRepository(Tag::class)->findOneBy([
                'name' => $tagDTO->name
            ]);

            if (null !== $existingTag) {
                return $existingTag;
            }

            $tag = new Tag();
            $tag->setName($tagDTO->name)
                ->setColorCode($tagDTO->colorCode);

            $errors = $this->validator->validate($tag, null, ['create']);
            ValidationFailureException::throwException($errors);

            $this->entityManager->persist($tag);
            if ($flush) {
                $this->entityManager->flush();
            }

            return $tag;
        } catch (ORMException $e) {
            $this->logger->critical(
                "Exception occured while creating tag." . $e->getMessage()
            );
            throw $e;
        }
    }

    public function updateTag(string $id, TagDTO $tagDTO): ?Tag
    {
        /** @var \App\Repository\TagRepository $tagRepository */
        $tagRepository = $this->entityManager->getRepository(Tag::class);
        $tag = $tagRepository->find($id);

        if ($tag === null) {
            return null;
        }

        $tag->setName($tagDTO->name)
            ->setColorCode($tagDTO->colorCode);

        $errors = $this->validator->validate($tag, null, ['update']);
        ValidationFailureException::throwException($errors);

        $this->entityManager->flush();

        return $tag;
    }

    public function deleteTag(int $id): bool
    {
        /** @var \App\Repository\TagRepository $tagRepository */
        $tagRepository = $this->entityManager->getRepository(Tag::class);
        $tag = $tagRepository->find($id);

        if ($tag === null) {
            return false;
        }

        $this->entityManager->remove($tag);
        $this->entityManager->flush();

        return true;
    }

    public function getTag(int $id): ?Tag
    {
        /** @var \App\Repository\TagRepository $tagRepository */
        $tagRepository = $this->entityManager->getRepository(Tag::class);

        return $tagRepository->find($id);
    }

    public function getAllTags(): array
    {
        /** @var \App\Repository\TagRepository $tagRepository */
        $tagRepository = $this->entityManager->getRepository(Tag::class);

        return $tagRepository->findAll();
    }

    public function getTagsCount(): int
    {
        /** @var \App\Repository\TagRepository $tagRepository */
        $tagRepository = $this->entityManager->getRepository(Tag::class);

        return $tagRepository->getTagsCount();
    }
}
