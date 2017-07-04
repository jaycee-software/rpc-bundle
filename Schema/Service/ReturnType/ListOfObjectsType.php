<?php

namespace JayceeSoftware\RpcBundle\Schema\Service\ReturnType;

class ListOfObjectsType extends ObjectsCollectionType
{
    public function __construct()
    {
        $pages = new ObjectType();
        $pages->setName('pages');

        $page = new IntegerType();
        $page->setName('page');
        $pages->addField($page);

        $limit = new IntegerType();
        $limit->setName('limit');
        $pages->addField($limit);

        $total = new IntegerType();
        $total->setName('total');
        $pages->addField($total);

        $this->additionalFields[] = $pages;
    }
}
