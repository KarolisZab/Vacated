<?php

namespace App\Controller\Admin;

use App\DTO\TagDTO;
use App\Service\TagManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/admin')]
class TagController extends AbstractController
{
    public function __construct(
        private TagManager $tagManager,
        private SerializerInterface $serializer,
        private Security $security
    ) {
    }

    #[Route('/tags', name: 'get_tags', methods: ['GET'])]
    public function getTags(Request $request)
    {
        $tags = $this->tagManager->getAllTags();

        return new JsonResponse($this->serializer->serialize($tags, 'json'), JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/tags/{id}', name: 'get_tag', methods: ['GET'])]
    public function getOneTag(string $id)
    {
        $tag = $this->tagManager->getTag($id);

        if ($tag === null) {
            return new JsonResponse('Tag not found', JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializer->serialize($tag, 'json'), JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/tags', name: 'create_tag', methods: ['POST'])]
    public function createTag(Request $request, #[MapRequestPayload()] TagDTO $tagDTO)
    {
        try {
            $tag = $this->tagManager->createOrGetTag($tagDTO);

            return new JsonResponse(
                $this->serializer->serialize($tag, 'json'),
                JsonResponse::HTTP_CREATED,
                [],
                true
            );
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 400);
        }
    }

    #[Route('/tags/{id}', name: 'update_tag', methods: ['PATCH'])]
    public function updateTag(Request $request, string $id, #[MapRequestPayload()] TagDTO $tagDTO)
    {
        try {
            $tag = $this->tagManager->updateTag($id, $tagDTO);

            if ($tag === null) {
                return new JsonResponse('Tag not found', JsonResponse::HTTP_NOT_FOUND);
            };

            return new JsonResponse(
                $this->serializer->serialize($tag, 'json'),
                JsonResponse::HTTP_OK,
                [],
                true
            );
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 400);
        }
    }

    #[Route('/tags/{id}', name: 'delete_tag', methods: ['DELETE'])]
    public function deleteTag(Request $request, string $id)
    {
        try {
            $tag = $this->tagManager->deleteTag($id);

            if ($tag === false) {
                return new JsonResponse('User not found', JsonResponse::HTTP_NOT_FOUND);
            }

            return new JsonResponse($this->serializer->serialize($tag, 'json'), JsonResponse::HTTP_OK, [], true);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode());
        }
    }
}
