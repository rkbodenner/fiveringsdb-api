<?php

declare(strict_types=1);

namespace Tests\AppBundle\Service\DeckCheck;

use AppBundle\Entity\Card;
use AppBundle\Entity\DeckCard;
use AppBundle\Service\DeckCheck\ConflictDeckCheck;
use AppBundle\Service\DeckValidator;

class ConflictDeckCheckTest extends AbstractDeckCheckTest
{
    function testTooFewConflict()
    {
        $this->assertCheck(
            new ConflictDeckCheck(),
            DeckValidator::TOO_FEW_CONFLICT,
            [
                new DeckCard(
                    $this->getCard(['side' => Card::SIDE_CONFLICT]),
                    39
                ),
            ]
        );
    }

    function testTooManyConflict()
    {
        $this->assertCheck(
            new ConflictDeckCheck(),
            DeckValidator::TOO_MANY_CONFLICT,
            [
                new DeckCard(
                    $this->getCard(['side' => Card::SIDE_CONFLICT]),
                    46
                ),
            ]
        );
    }

    function testTooManyCharacterInConflict()
    {
        $this->assertCheck(
            new ConflictDeckCheck(),
            DeckValidator::TOO_MANY_CHARACTER_IN_CONFLICT,
            [
                new DeckCard(
                    $this->getCard(['side' => Card::SIDE_CONFLICT, 'type' => Card::TYPE_ATTACHMENT]),
                    29
                ),
                new DeckCard(
                    $this->getCard(['side' => Card::SIDE_CONFLICT, 'type' => Card::TYPE_CHARACTER]),
                    11
                ),
            ]
        );
    }

    function testNotEnoughInfluence()
    {
        $this->assertCheck(
            new ConflictDeckCheck(),
            DeckValidator::NOT_ENOUGH_INFLUENCE,
            [
                new DeckCard(
                    $this->getCard(['side' => Card::SIDE_PROVINCE, 'type' => Card::TYPE_STRONGHOLD, 'clan' => Card::CLAN_CRAB, 'influence_pool' => 10]),
                    1
                ),
                new DeckCard(
                    $this->getCard(['side' => Card::SIDE_CONFLICT, 'type' => Card::TYPE_ATTACHMENT, 'clan' => Card::CLAN_CRANE, 'influence_cost' => 1]),
                    40
                ),
            ]
        );
    }

    function testTooManyOffClans()
    {
        $this->assertCheck(
            new ConflictDeckCheck(),
            DeckValidator::TOO_MANY_OFF_CLANS,
            [
                new DeckCard(
                    $this->getCard(['side' => Card::SIDE_PROVINCE, 'type' => Card::TYPE_STRONGHOLD, 'clan' => Card::CLAN_CRAB, 'influence_pool' => 10]),
                    1
                ),
                new DeckCard(
                    $this->getCard(['side' => Card::SIDE_CONFLICT, 'type' => Card::TYPE_ATTACHMENT, 'clan' => Card::CLAN_CRAB]),
                    40
                ),
                new DeckCard(
                    $this->getCard(['side' => Card::SIDE_CONFLICT, 'type' => Card::TYPE_ATTACHMENT, 'clan' => Card::CLAN_CRANE, 'influence_cost' => 1]),
                    1
                ),
                new DeckCard(
                    $this->getCard(['side' => Card::SIDE_CONFLICT, 'type' => Card::TYPE_ATTACHMENT, 'clan' => Card::CLAN_DRAGON, 'influence_cost' => 1]),
                    1
                ),
            ]
        );
    }
}
