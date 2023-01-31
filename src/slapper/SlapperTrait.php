<?php

declare(strict_types=1);

namespace slapper;

use pocketmine\player\Player;

/**
 * Trait containing methods used in various Slappers.
 */
trait SlapperTrait {

    protected bool $networkPropertiesDirty = false;

    /**
     * @var true[]
     * @phpstan-var array<string, true>
     */
    protected array $commands = [];

    protected string $version;

    /**
     * @return string
     */
    abstract public function getNameTag(): string;

    public function tryChangeMovement(): void {
        //NOOP
    }

    public function prepareMetadata(): void {
        $this->networkPropertiesDirty = true;
    }

    public function sendData(?array $playerList, ?array $data = null): void {
        //NOOP
    }

    public function getDisplayNameTag(Player $player): string {
        $vars = [
            "{name}" => $player->getName(),
            "{display_name}" => $player->getDisplayName(),
            "{nametag}" => $player->getNameTag()
        ];
        return str_replace(array_keys($vars), array_values($vars), $this->getNameTag());
    }

    /** @return string[] */
    public function getCommands(): array{
        return array_keys($this->commands);
    }

    public function addCommand(string $command): void{
        $this->commands[$command] = true;
    }

    public function hasCommand(string $command): bool{
        return isset($this->commands[$command]);
    }

    public function removeCommand(string $command): void{
        unset($this->commands[$command]);
    }

    public function setSlapperVersion(string $version): void{
        $this->version = $version;
    }

    public function getSlapperVersion(): string{
        return $this->version;
    }
}
