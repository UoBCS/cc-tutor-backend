<?php

namespace App\Core\Parser;

use Tree\Node\NodeInterface;
use Tree\Visitor\Visitor;

class ParseTreeSerializeVisitor implements Visitor
{
    private $transformNodeValue;

    public function __construct($transformNodeValue = null)
    {
        $this->transformNodeValue = $transformNodeValue === null
                                    ? function ($x) { return $x; }
                                    : $transformNodeValue;
    }

    public function visit(NodeInterface $node)
    {
        $nodeValue = $node->getValue();
        $transformer = $this->transformNodeValue;

        $nodes = [
            'node'     => $transformer($node->getValue()), //call_user_func([$this, 'transformNodeValue'], $node->getValue()),
            'children' => []
        ];

        foreach ($node->getChildren() as $child) {
            $nodes['children'][] = $child->accept($this);
        }

        return $nodes;
    }
}
