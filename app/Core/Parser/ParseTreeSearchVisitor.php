<?php

namespace App\Core\Parser;

use Tree\Node\NodeInterface;
use Tree\Visitor\Visitor;

class ParseTreeSearchVisitor implements Visitor
{
    private $nodeToSearch;

    public function __construct($nodeToSearch)
    {
        $this->nodeToSearch = $nodeToSearch;
        $this->foundNode = null;
    }

    public function visit(NodeInterface $node)
    {
        if ($node->getValue()->equals($this->nodeToSearch)) {
            $this->foundNode = $node;
        }

        foreach ($node->getChildren() as $child) {
            $child->accept($this);
            //$nodes['children'][] = $child->accept($this);
        }

        return $this->foundNode;
    }
}
