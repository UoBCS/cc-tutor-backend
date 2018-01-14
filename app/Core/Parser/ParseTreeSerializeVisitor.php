<?php

namespace App\Core\Parser;

use Tree\Node\NodeInterface;
use Tree\Visitor\Visitor;

class ParseTreeSerializeVisitor implements Visitor
{
    public function visit(NodeInterface $node)
    {
        $nodes = [
            'node'     => $node,
            'children' => []
        ];

        foreach ($node->getChildren() as $child) {
            $nodes['children'][] = $child->accept($this);
        }

        return $nodes;
    }
}
