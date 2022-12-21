<?php

namespace App\Service;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use App\Repository\TypeRepository;

class TypeService
{

    private FilesystemAdapter $cache;
    private TypeRepository $typeRepository;

    public function __construct(TypeRepository $typeRepository)
    {
        $this->cache = new FilesystemAdapter();
        $this->typeRepository = $typeRepository;
    }

    public function getTypeCached()
    {
        $value = $this->cache->get('type_key', function (ItemInterface $item) {
            $item->expiresAfter(3600);
            return $this->typeRepository->findBy(array());
        });
        $this->cache->delete('type_key');
        return $value;
    }
}
