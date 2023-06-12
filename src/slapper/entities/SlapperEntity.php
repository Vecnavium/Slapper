<?php

declare(strict_types=1);

namespace slapper\entities;

use ErrorException;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\MetadataProperty;
use pocketmine\player\Player;
use pocketmine\world\particle\FloatingTextParticle;
use slapper\SlapperTrait;
use slapper\SlapperInterface;
use TypeError;

class SlapperEntity extends Entity implements SlapperInterface{
    use SlapperTrait;

    public static function getNetworkTypeId(): string{
        return static::TYPE_ID;
    }

    const TYPE_ID = "";
    const HEIGHT = 0;

    public float $width = 1; //BC and polyfill

    private CompoundTag $namedTagHack;

    private FloatingTextParticle $particle;

	private bool $nameTagDirty = false;

    public function __construct(Location $location, ?CompoundTag $nbt = null) {
        $this->particle = new FloatingTextParticle('');
        parent::__construct($location, $nbt);
    }

    public function initEntity(CompoundTag $nbt): void {
        parent::initEntity($nbt);
        $this->namedTagHack = $nbt;
        if(($commandsTag = $nbt->getTag('Commands')) instanceof ListTag or $commandsTag instanceof CompoundTag){
            /** @var StringTag $stringTag */
            foreach($commandsTag as $stringTag){
                $this->commands[$stringTag->getValue()] = true;
            }
        }
        $this->version = $nbt->getString('SlapperVersion', '');
        $this->setNoClientPredictions();
        $this->setNameTagVisible(false);

		$this->setScale($nbt->getFloat('Scale', 1));
    }

    //For backwards-compatibility
    public function saveNBT(): CompoundTag{
        $nbt = parent::saveNBT();
		$nbt = $nbt->merge($this->namedTagHack);
        $commandsTag = new ListTag([], NBT::TAG_String);
        foreach($this->commands as $command => $bool){
            $commandsTag->push(new StringTag($command));
        }
		$nbt->setFloat('Scale', $this->getScale());
		$nbt->setString('SlapperVersion', $this->version);
		$nbt->setTag('Commands', $commandsTag);
        return $nbt;
    }

	public function setNameTag(string $name): void {
		parent::setNameTag($name);
		$this->nameTagDirty = true;
	}

    protected function sendSpawnPacket(Player $player): void {
        parent::sendSpawnPacket($player);

        $this->particle->setTitle($this->getSlapperDisplayName($player));
        $this->getWorld()->addParticle($this->location->asVector3()->add(0, static::HEIGHT, 0), $this->particle, [$player]);
    }

    public function despawnFrom(Player $player, bool $send = true): void {
        parent::despawnFrom($player, $send);
        $this->particle->setInvisible(true);
        $this->getWorld()->addParticle($this->location->asVector3()->add(0, static::HEIGHT, 0), $this->particle, [$player]);
        $this->particle->setInvisible(false);
    }

    /**
     * @param Player[]|null $targets
     * @param MetadataProperty[] $data
     */
    public function sendData(?array $targets, ?array $data = null): void{
        $targets ??= $this->hasSpawned;
        $data ??= $this->getAllNetworkData();
        parent::sendData($targets, $data);
        if($this->nameTagDirty){
            $this->spawnParticleToPlayers($targets);
			$this->nameTagDirty = false;
        }
    }

    public function broadcastMovement(bool $teleport = false): void {
        parent::broadcastMovement($teleport);
        $this->spawnParticleToPlayers($this->hasSpawned);
    }

    public function getInitialSizeInfo(): EntitySizeInfo{ return new EntitySizeInfo(static::HEIGHT, $this->width); }

    /** @param Player[] $players */
    private function spawnParticleToPlayers(array $players): void{
        $world = $this->getWorld();
        $particlePos = $this->location->asVector3()->add(0, static::HEIGHT, 0);
        foreach($players as $player){
            $this->particle->setTitle($this->getSlapperDisplayName($player));
            $world->addParticle($particlePos, $this->particle, [$player]);
        }
    }

    //For backwards-compatibility
    public function __get(string $name): CompoundTag{
        if($name === 'namedtag'){
            return $this->namedTagHack;
        }
        throw new ErrorException("Undefined property: " . get_class($this) . "::\$" . $name);
    }
    
    //For backwards-compatibility
    public function __set(string $name, mixed $value): void{
        if($name === 'namedtag'){
            if(!$value instanceof CompoundTag){
                throw new TypeError('Typed property ' . get_class($this) . "::\$namedtag must be " . CompoundTag::class . ", " . gettype($value) . "used");
            }
            $this->namedTagHack = $value;
            return;
        }
        throw new ErrorException("Undefined property: " . get_class($this) . "::\$" . $name);
    }

    //For backwards-compatibility
    public function __isset(string $name): bool{
        return $name === 'namedtag';
    }

	protected function syncNetworkData(EntityMetadataCollection $properties): void {
		parent::syncNetworkData($properties);
		$properties->setString(EntityMetadataProperties::NAMETAG, "");
	}

    protected function getInitialDragMultiplier(): float{
        return 0;
    }

    protected function getInitialGravity(): float{
        return 0;
    }
}
