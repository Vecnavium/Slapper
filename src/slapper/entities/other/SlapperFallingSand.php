<?php

declare(strict_types=1);

namespace slapper\entities\other;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\entity\Location;
use pocketmine\entity\object\FallingBlock;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use slapper\entities\SlapperEntity;

class SlapperFallingSand extends SlapperEntity {

    const TYPE_ID = EntityLegacyIds::FALLING_BLOCK;
    const HEIGHT = 0.98;

    protected Block $block;

    public function initEntity(CompoundTag $nbt): void{
        parent::initEntity($nbt);

        $blockFactory = BlockFactory::getInstance();
        if(!($blockIdTag = $nbt->getTag("Tile"))){
            $this->block = FallingBlock::parseBlockNBT($blockFactory, $nbt->setTag('Tile', new ByteTag(1)));
        }else{
            $this->block = $blockFactory->get($blockIdTag->getValue(), 0);
        }
    }

    protected function syncNetworkData(EntityMetadataCollection $properties) : void{
        parent::syncNetworkData($properties);

        $properties->setInt(EntityMetadataProperties::VARIANT, RuntimeBlockMapping::getInstance()->toRuntimeId($this->block->getFullId()));
    }

    public function saveNBT(): CompoundTag {
        $nbt = parent::saveNBT();
        $nbt->setByte("TileID", $this->block->getId());
        $nbt->setByte("Data", $this->block->getMeta());

        return $nbt;
    }

    public function setBlock(Block $block): void{
        $this->block = $block;
        $this->networkPropertiesDirty = true;
    }

    public function getLocation(): Location
    {
        $pos = clone $this->location;
        return Location::fromObject($pos->asPosition()->add(0.5, 0, 0.5), $this->location->world, 1, 1);
    }
}
