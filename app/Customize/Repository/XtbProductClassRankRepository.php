<?php

namespace Customize\Repository;

use Customize\Entity\XtbProductClassRank;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Eccube\Repository\AbstractRepository;

/**
 * @method XtbProductClassRank|null find($id, $lockMode = null, $lockVersion = null)
 * @method XtbProductClassRank|null findOneBy(array $criteria, array $orderBy = null)
 * @method XtbProductClassRank[]    findAll()
 * @method XtbProductClassRank[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class XtbProductClassRankRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, XtbProductClassRank::class);
    }
}
