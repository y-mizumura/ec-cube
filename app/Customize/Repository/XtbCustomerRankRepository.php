<?php

namespace Customize\Repository;

use Customize\Entity\XtbCustomerRank;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Eccube\Repository\AbstractRepository;

/**
 * @method XtbCustomerRank|null find($id, $lockMode = null, $lockVersion = null)
 * @method XtbCustomerRank|null findOneBy(array $criteria, array $orderBy = null)
 * @method XtbCustomerRank[]    findAll()
 * @method XtbCustomerRank[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class XtbCustomerRankRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, XtbCustomerRank::class);
    }

/**
     * 会員ランク一覧を取得する.
     *
     * @return XtbCustomerRank[] 料金区の配列
     */
    public function getList()
    {
        $qb = $this->createQueryBuilder('t')->orderBy('t.sort_no', 'DESC');
        return $qb->getQuery()->getResult();
    }
    
    /**
     * 会員ランクを保存する.
     *
     * @param  XtbCustomerRank $customerRank 会員ランク
     */
    public function save($customerRank)
    {
        if (!$customerRank->getId()) {
            $sortNoTop = $this->findOneBy([], ['sort_no' => 'DESC']);
            $sort_no = 0;
            if (!is_null($sortNoTop)) {
                $sort_no = $sortNoTop->getSortNo();
            }

            $customerRank->setSortNo($sort_no + 1);
        }

        $em = $this->getEntityManager();
        $em->persist($customerRank);
        $em->flush($customerRank);
    }
    
    /**
     * 会員ランクを削除する.
     *
     * @param  XChargeCategory $customerRank 削除対象
     * 
     * @throws ForeignKeyConstraintViolationException 外部キー制約違反の場合
     * @throws DriverException SQLiteの場合, 外部キー制約違反が発生すると, DriverExceptionをthrowします.
     * 
     */
    public function delete($customerRank)
    {
        $em = $this->getEntityManager();
        
        // トランザクション（開始）
        $em->beginTransaction();

        // 並び替えを整理
        $this
            ->createQueryBuilder('t')
            ->update()
            ->set('t.sort_no', 't.sort_no - 1')
            ->where('t.sort_no > :sort_no')
            ->setParameter('sort_no', $customerRank->getSortNo())
            ->getQuery()
            ->execute();

        $em->remove($customerRank);
        $em->flush($customerRank);

        // トランザクション（終了）
        $em->commit();
    }
}
