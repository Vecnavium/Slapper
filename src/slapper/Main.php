<?php

declare(strict_types=1);

namespace slapper;

use pocketmine\block\BlockFactory;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\Listener;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\LegacyStringToItemParserException;
use pocketmine\item\StringToItemParser;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use pocketmine\world\Location;
use pocketmine\world\World;
use slapper\entities\other\SlapperBoat;
use slapper\entities\other\SlapperFallingSand;
use slapper\entities\other\SlapperMinecart;
use slapper\entities\other\SlapperPrimedTNT;
use slapper\entities\SlapperBat;
use slapper\entities\SlapperBlaze;
use slapper\entities\SlapperCaveSpider;
use slapper\entities\SlapperChicken;
use slapper\entities\SlapperCow;
use slapper\entities\SlapperCreeper;
use slapper\entities\SlapperDonkey;
use slapper\entities\SlapperElderGuardian;
use slapper\entities\SlapperEnderman;
use slapper\entities\SlapperEndermite;
use slapper\entities\SlapperEntity;
use slapper\entities\SlapperEvoker;
use slapper\entities\SlapperGhast;
use slapper\entities\SlapperGuardian;
use slapper\entities\SlapperHorse;
use slapper\entities\SlapperHuman;
use slapper\entities\SlapperIronGolem;
use slapper\entities\SlapperHusk;
use slapper\entities\SlapperLavaSlime;
use slapper\entities\SlapperLlama;
use slapper\entities\SlapperMule;
use slapper\entities\SlapperMushroomCow;
use slapper\entities\SlapperOcelot;
use slapper\entities\SlapperPig;
use slapper\entities\SlapperPigZombie;
use slapper\entities\SlapperPolarBear;
use slapper\entities\SlapperRabbit;
use slapper\entities\SlapperSheep;
use slapper\entities\SlapperShulker;
use slapper\entities\SlapperSilverfish;
use slapper\entities\SlapperSkeletonHorse;
use slapper\entities\SlapperSkeleton;
use slapper\entities\SlapperSlime;
use slapper\entities\SlapperSnowman;
use slapper\entities\SlapperSpider;
use slapper\entities\SlapperSquid;
use slapper\entities\SlapperStray;
use slapper\entities\SlapperVex;
use slapper\entities\SlapperVillager;
use slapper\entities\SlapperVindicator;
use slapper\entities\SlapperWitch;
use slapper\entities\SlapperWither;
use slapper\entities\SlapperWitherSkeleton;
use slapper\entities\SlapperWolf;
use slapper\entities\SlapperZombie;
use slapper\entities\SlapperZombieHorse;
use slapper\entities\SlapperZombieVillager;
use slapper\events\SlapperCreationEvent;
use slapper\events\SlapperDeletionEvent;
use slapper\events\SlapperHitEvent;


class Main extends PluginBase implements Listener {

    const ENTITY_TYPES = [
        "Chicken", "Pig", "Sheep", "Cow",
        "MushroomCow", "Wolf", "Enderman", "Spider",
        "Skeleton", "PigZombie", "Creeper", "Slime",
        "Silverfish", "Villager", "Zombie", "Human",
        "Bat", "CaveSpider", "LavaSlime", "Ghast",
        "Ocelot", "Blaze", "ZombieVillager", "Snowman",
        "Minecart", "FallingSand", "Boat", "PrimedTNT",
        "Horse", "Donkey", "Mule", "SkeletonHorse",
        "ZombieHorse", "Witch", "Rabbit", "Stray",
        "Husk", "WitherSkeleton", "IronGolem", "Snowman",
        "LavaSlime", "Squid", "ElderGuardian", "Endermite",
        "Evoker", "Guardian", "PolarBear", "Shulker",
        "Vex", "Vindicator", "Wither", "Llama"
    ];

    const ENTITY_ALIASES = [
        "MagmaCube" => "LavaSlime",
        "ZombiePigman" => "PigZombie",
        "Mooshroom" => "MushroomCow",
        "Player" => "Human",
        "VillagerZombie" => "ZombieVillager",
        "SnowGolem" => "Snowman",
        "FallingBlock" => "FallingSand",
        "FakeBlock" => "FallingSand",
        "VillagerGolem" => "IronGolem",
        "EGuardian" => "ElderGuardian",
        "Emite" => "Endermite"
    ];

    /** @var array<string, true> */
    public $hitSessions = [];
    /** @var array<string, true> */
    public $idSessions = [];
    /** @var string */
    public $prefix = TextFormat::GREEN . "[" . TextFormat::YELLOW . "Slapper" . TextFormat::GREEN . "] ";
    /** @var string */
    public $noperm = TextFormat::GREEN . "[" . TextFormat::YELLOW . "Slapper" . TextFormat::GREEN . "] You don't have permission.";
    /** @var string */
    public $helpHeader =
        TextFormat::YELLOW . "---------- " .
        TextFormat::GREEN . "[" . TextFormat::YELLOW . "Slapper Help" . TextFormat::GREEN . "] " .
        TextFormat::YELLOW . "----------";

    /** @var string[] */
    public $mainArgs = [
        "help: /slapper help",
        "spawn: /slapper spawn <type> [name]",
        "edit: /slapper edit [id] [args...]",
        "id: /slapper id",
        "remove: /slapper remove [id]",
        "version: /slapper version",
        "cancel: /slapper cancel",
    ];
    /** @var string[] */
    public $editArgs = [
        "helmet: /slapper edit <eid> helmet <id>",
        "chestplate: /slapper edit <eid> chestplate <id>",
        "leggings: /slapper edit <eid> leggings <id>",
        "boots: /slapper edit <eid> boots <id>",
        "skin: /slapper edit <eid> skin",
        "name: /slapper edit <eid> name <name>",
        "addcommand: /slapper edit <eid> addcommand <command>",
        "delcommand: /slapper edit <eid> delcommand <command>",
        "listcommands: /slapper edit <eid> listcommands",
        "blockid: /slapper edit <eid> block <id[:meta]>",
        "scale: /slapper edit <eid> scale <size>",
        "tphere: /slapper edit <eid> tphere",
        "tpto: /slapper edit <eid> tpto",
        "menuname: /slapper edit <eid> menuname <name/remove>"
    ];

    private SlapperCommandSender $commandSender;

    /**
     * @return void
     */
    public function onEnable(): void {
        $this->commandSender = new SlapperCommandSender($this);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->registerEntities();
    }
    public function registerEntities(): void {
        $entityFactory = EntityFactory::getInstance();
        /** @var class-string<SlapperEntity> $className */
        foreach ([
             SlapperCreeper::class, SlapperBat::class, SlapperSheep::class,
             SlapperPigZombie::class, SlapperGhast::class, SlapperBlaze::class,
             SlapperIronGolem::class, SlapperSnowman::class, SlapperOcelot::class,
             SlapperZombieVillager::class, SlapperCow::class,
             SlapperZombie::class, SlapperSquid::class, SlapperVillager::class,
             SlapperSpider::class, SlapperPig::class, SlapperMushroomCow::class,
             SlapperWolf::class, SlapperLavaSlime::class, SlapperSilverfish::class,
             SlapperSkeleton::class, SlapperSlime::class, SlapperChicken::class,
             SlapperEnderman::class, SlapperCaveSpider::class, SlapperBoat::class,
             SlapperMinecart::class, SlapperMule::class, SlapperWitch::class,
             SlapperPrimedTNT::class, SlapperHorse::class, SlapperDonkey::class,
             SlapperSkeletonHorse::class, SlapperZombieHorse::class, SlapperRabbit::class,
             SlapperStray::class, SlapperHusk::class, SlapperWitherSkeleton::class,
             SlapperFallingSand::class, SlapperElderGuardian::class, SlapperEndermite::class,
             SlapperEvoker::class, SlapperGuardian::class, SlapperLlama::class,
             SlapperPolarBear::class, SlapperShulker::class, SlapperVex::class,
             SlapperVindicator::class, SlapperWither::class
        ] as $className){
            $stringPos = strpos($className, 'Slapper');
            if($stringPos === false){
                throw new AssumptionFailedError("$className should always contain the word 'Slapper'");
            }
            $entityName = substr($className, $stringPos);
            $entityFactory->register($className, static function(World $world, CompoundTag $nbt) use($className): SlapperEntity{
                /** @var SlapperEntity $entityClass */
                $entityClass = new $className(EntityDataHelper::parseLocation($nbt, $world), $nbt);
                return $entityClass;
            }, [$entityName], $className::TYPE_ID);
        }
        $entityFactory->register(SlapperHuman::class, static function(World $world, CompoundTag $nbt): SlapperHuman{
            return new SlapperHuman(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), $nbt);
        }, ['Human']);
    }


    /**
     * @param CommandSender $sender
     * @param Command       $command
     * @param string        $label
     * @param string[]      $args
     *
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        switch (strtolower($command->getName())) {
            case "nothing":
                return true;
            case "rca":
                if (count($args) < 2) {
                    $sender->sendMessage($this->prefix . "Please enter a player and a command.");
                    return true;
                }
                $player = $this->getServer()->getPlayerByPrefix(array_shift($args));
                if ($player instanceof Player) {
                    $this->getServer()->dispatchCommand($player, trim(implode(" ", $args)));
                    return true;
                } else {
                    $sender->sendMessage($this->prefix . "Player not found.");
                    return true;
                }
            case "slapper":
                if ($sender instanceof Player) {
                    if (!isset($args[0])) {
                            $sender->sendMessage($this->prefix . "Please type '/slapper help'.");
                            return true;
                        }
                    }
                        case "id":
                            if (!$sender->hasPermission("slapper.id")) {
                                $sender->sendMessage($this->noperm);
                                return true;
                            }
                            $this->idSessions[$sender->getName()] = true;
                            $sender->sendMessage($this->prefix . "Hit an entity to get its ID!");
                            return true;
                        case "version":
                            if (!$sender->hasPermission("slapper.version")) {
                                $sender->sendMessage($this->noperm);
                                return true;
                            }
                            $desc = $this->getDescription();
                            $sender->sendMessage($this->prefix . TextFormat::BLUE . $desc->getName() . " " . $desc->getVersion() . " " . TextFormat::GREEN . "by " . TextFormat::GOLD . "jojoe77777");
                            return true;
                        case "cancel":
                        case "stopremove":
                        case "stopid":
                            unset($this->hitSessions[$sender->getName()]);
                            unset($this->idSessions[$sender->getName()]);
                            $sender->sendMessage($this->prefix . "Cancelled.");
                            return true;
                        case "remove":
                            if (!$sender->hasPermission("slapper.remove")) {
                                $sender->sendMessage($this->noperm);
                                return true;
                            }
                            if (!isset($args[0])) {
                                $this->hitSessions[$sender->getName()] = true;
                                $sender->sendMessage($this->prefix . "Hit an entity to remove it.");
                                return true;
                            }
                            $entity = $sender->getWorld()->getEntity((int) $args[0]);
                            if ($entity !== null) {
                                if ($entity instanceof SlapperEntity || $entity instanceof SlapperHuman) {
                                    (new SlapperDeletionEvent($entity))->call();
                                    $entity->close();
                                    $sender->sendMessage($this->prefix . "Entity removed.");
                                } else {
                                    $sender->sendMessage($this->prefix . "That entity is not handled by Slapper.");
                                }
                            } else {
                                $sender->sendMessage($this->prefix . "Entity does not exist.");
                            }
                            return true;
                        case "edit":
                            if (!$sender->hasPermission("slapper.edit")) {
                                $sender->sendMessage($this->noperm);
                                return true;
                            }
                            if (isset($args[0])) {
                                $world = $sender->getWorld();
                                $entity = $world->getEntity((int) $args[0]);
                                if ($entity !== null) {
                                    if ($entity instanceof SlapperInterface) {
                                        if (isset($args[1])) {
                                            switch ($args[1]) {
                                                case "helm":
                                                case "helmet":
                                                case "head":
                                                case "hat":
                                                case "cap":
                                                    if ($entity instanceof SlapperHuman) {
                                                        if (isset($args[2])) {
                                                            try{
                                                                $item = StringToItemParser::getInstance()->parse($args[2]) ?? LegacyStringToItemParser::getInstance()->parse($args[2]);
                                                            }catch(LegacyStringToItemParserException){
                                                                $sender->sendMessage($this->prefix . "There is no such item with name $args[2]");
                                                                return true;
                                                            }
                                                            $entity->getArmorInventory()->setHelmet($item);
                                                            $sender->sendMessage($this->prefix . "Helmet updated.");
                                                        } else {
                                                            $sender->sendMessage($this->prefix . "Please enter an item ID.");
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "That entity can not wear armor.");
                                                    }
                                                    return true;
                                                case "chest":
                                                case "shirt":
                                                case "chestplate":
                                                    if ($entity instanceof SlapperHuman) {
                                                        if (isset($args[2])) {
                                                            try{
                                                                $item = StringToItemParser::getInstance()->parse($args[2]) ?? LegacyStringToItemParser::getInstance()->parse($args[2]);
                                                            }catch(LegacyStringToItemParserException){
                                                                $sender->sendMessage($this->prefix . "There is no such item with name $args[2]");
                                                                return true;
                                                            }
                                                            $entity->getArmorInventory()->setChestplate($item);
                                                            $sender->sendMessage($this->prefix . "Chestplate updated.");
                                                        } else {
                                                            $sender->sendMessage($this->prefix . "Please enter an item ID.");
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "That entity can not wear armor.");
                                                    }
                                                    return true;
                                                case "pants":
                                                case "legs":
                                                case "leggings":
                                                    if ($entity instanceof SlapperHuman) {
                                                        if (isset($args[2])) {
                                                            try{
                                                                $item = StringToItemParser::getInstance()->parse($args[2]) ?? LegacyStringToItemParser::getInstance()->parse($args[2]);
                                                            }catch(LegacyStringToItemParserException){
                                                                $sender->sendMessage($this->prefix . "There is no such item with name $args[2]");
                                                                return true;
                                                            }
                                                            $entity->getArmorInventory()->setLeggings($item);
                                                            $sender->sendMessage($this->prefix . "Leggings updated.");
                                                        } else {
                                                            $sender->sendMessage($this->prefix . "Please enter an item ID.");
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "That entity can not wear armor.");
                                                    }
                                                    return true;
                                                case "feet":
                                                case "boots":
                                                case "shoes":
                                                    if ($entity instanceof SlapperHuman) {
                                                        if (isset($args[2])) {
                                                            try{
                                                                $item = StringToItemParser::getInstance()->parse($args[2]) ?? LegacyStringToItemParser::getInstance()->parse($args[2]);
                                                            }catch(LegacyStringToItemParserException){
                                                                $sender->sendMessage($this->prefix . "There is no such item with name $args[2]");
                                                                return true;
                                                            }
                                                            $entity->getArmorInventory()->setBoots($item);
                                                            $sender->sendMessage($this->prefix . "Boots updated.");
                                                        } else {
                                                            $sender->sendMessage($this->prefix . "Please enter an item ID.");
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "That entity can not wear armor.");
                                                    }
                                                    return true;
                                                case "hand":
                                                case "item":
                                                case "holding":
                                                case "arm":
                                                case "held":
                                                    if ($entity instanceof SlapperHuman) {
                                                        if (isset($args[2])) {
                                                            try{
                                                                $item = StringToItemParser::getInstance()->parse($args[2]) ?? LegacyStringToItemParser::getInstance()->parse($args[2]);
                                                            }catch(LegacyStringToItemParserException){
                                                                $sender->sendMessage($this->prefix . "There is no such item with name $args[2]");
                                                                return true;
                                                            }
                                                            $entity->getInventory()->setItemInHand($item);
                                                            $sender->sendMessage($this->prefix . "Item updated.");
                                                        } else {
                                                            $sender->sendMessage($this->prefix . "Please enter an item ID.");
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "That entity can not wear armor.");
                                                    }
                                                    return true;
                                                case "setskin":
                                                case "changeskin":
                                                case "editskin";
                                                case "skin":
                                                    if ($entity instanceof SlapperHuman) {
                                                        $entity->setSkin($sender->getSkin());
                                                        $entity->sendData($entity->getViewers());
                                                        $sender->sendMessage($this->prefix . "Skin updated.");
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "That entity can't have a skin.");
                                                    }
                                                    return true;
                                                case "name":
                                                case "customname":
                                                    if (isset($args[2])) {
                                                        array_shift($args);
                                                        array_shift($args);
                                                        $entity->setNameTag(str_replace(["{color}", "{line}"], ["§", "\n"], trim(implode(" ", $args))));
                                                        $entity->sendData($entity->getViewers());
                                                        $sender->sendMessage($this->prefix . "Name updated.");
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "Please enter a name.");
                                                    }
                                                    return true;
                                                case "listname":
                                                case "nameonlist":
                                                case "menuname":
                                                    if ($entity instanceof SlapperHuman) {
                                                        if (isset($args[2])) {
                                                            $type = 0;
                                                            array_shift($args);
                                                            array_shift($args);
                                                            $input = trim(implode(" ", $args));
                                                            switch (strtolower($input)) {
                                                                case "remove":
                                                                case "":
                                                                case "disable":
                                                                case "off":
                                                                case "hide":
                                                                    $type = 1;
                                                            }
                                                            if ($type === 0) {
                                                                $entity->setMenuName($input);
                                                            } else {
                                                                $entity->setMenuName("");
                                                            }
                                                            $entity->respawnToAll();
                                                            $sender->sendMessage($this->prefix . "Menu name updated.");
                                                        } else {
                                                            $sender->sendMessage($this->prefix . "Please enter a menu name.");
                                                            return true;
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "That entity can not have a menu name.");
                                                    }
                                                    return true;
                                                case "addc":
                                                case "addcmd":
                                                case "addcommand":
                                                    if (isset($args[2])) {
                                                        array_shift($args);
                                                        array_shift($args);
                                                        $input = trim(implode(" ", $args));

                                                        if ($entity->hasCommand($input)) {
                                                            $sender->sendMessage($this->prefix . "That command has already been added.");
                                                            return true;
                                                        }
                                                        $entity->addCommand($input);
                                                        $sender->sendMessage($this->prefix . "Command added.");
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "Please enter a command.");
                                                    }
                                                    return true;
                                                case "delc":
                                                case "delcmd":
                                                case "delcommand":
                                                case "removecommand":
                                                    if (isset($args[2])) {
                                                        array_shift($args);
                                                        array_shift($args);
                                                        $input = trim(implode(" ", $args));

                                                        $entity->removeCommand($input);
                                                        $sender->sendMessage($this->prefix . "Command removed.");
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "Please enter a command.");
                                                    }
                                                    return true;
                                                case "listcommands":
                                                case "listcmds":
                                                case "listcs":
                                                    $commands = $entity->getCommands();
                                                    if (count($commands) > 0) {
                                                        $id = 0;

                                                        foreach ($commands as $slapperCommand) {
                                                            $id++;
                                                            $sender->sendMessage(TextFormat::GREEN . "[" . TextFormat::YELLOW . "S" . TextFormat::GREEN . "] " . TextFormat::YELLOW . $id . ". " . TextFormat::GREEN . $slapperCommand . "\n");
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "That entity does not have any commands.");
                                                    }
                                                    return true;
                                                case "block":
                                                case "tile":
                                                case "blockid":
                                                case "tileid":
                                                    if (isset($args[2])) {
                                                        if ($entity instanceof SlapperFallingSand) {
                                                            $data = explode(":", $args[2]);
                                                            $entity->setBlock(BlockFactory::getInstance()->get((int) ($data[0] ?? 1), (int) ($data[1] ?? 0)));
                                                            $sender->sendMessage($this->prefix . "Block updated.");
                                                        } else {
                                                            $sender->sendMessage($this->prefix . "That entity is not a block.");
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "Please enter a value.");
                                                    }
                                                    return true;
                                                case "teleporthere":
                                                case "tphere":
                                                case "movehere":
                                                case "bringhere":
                                                    $entity->teleport($sender->getLocation());
                                                    $sender->sendMessage($this->prefix . "Teleported entity to you.");
                                                    $entity->respawnToAll();
                                                    return true;
                                                case "teleportto":
                                                case "tpto":
                                                case "goto":
                                                case "teleport":
                                                case "tp":
                                                    $sender->teleport($entity->getLocation());
                                                    $sender->sendMessage($this->prefix . "Teleported you to entity.");
                                                    return true;
                                                case "scale":
                                                case "size":
                                                    if (isset($args[2])) {
                                                        $scale = (float) $args[2];
                                                        $entity->setScale($scale);
                                                        $sender->sendMessage($this->prefix . "Updated scale.");
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "Please enter a value.");
                                                    }
                                                    return true;
                                                default:
                                                    $sender->sendMessage($this->prefix . "Unknown command.");
                                                    return true;
                                            }
                                        } else {
                                            $sender->sendMessage($this->helpHeader);
                                            foreach ($this->editArgs as $msgArg) {
                                                $sender->sendMessage(str_replace("<eid>", $args[0], TextFormat::GREEN . " - " . $msgArg . "\n"));
                                            }
                                            return true;
                                        }
                                    } else {
                                        $sender->sendMessage($this->prefix . "That entity is not handled by Slapper.");
                                    }
                                } else {
                                    $sender->sendMessage($this->prefix . "Entity does not exist.");
                                }
                                return true;
                            } else {
                                $sender->sendMessage($this->helpHeader);
                                foreach ($this->editArgs as $msgArg) {
                                    $sender->sendMessage(TextFormat::GREEN . " - " . $msgArg . "\n");
                                }
                                return true;
                            }
                        case "help":
                        case "?":
                            $sender->sendMessage($this->helpHeader);
                            foreach ($this->mainArgs as $msgArg) {
                                $sender->sendMessage(TextFormat::GREEN . " - " . $msgArg . "\n");
                            }
                            return true;
                        case "add":
                        case "make":
                        case "create":
                        case "spawn":
                        case "apawn":
                        case "spanw":
                            $type = array_shift($args);
                            $name = str_replace(["{color}", "{line}"], ["§", "\n"], trim(implode(" ", $args)));
                            if ($type === null || trim($type) === "") {
                                $sender->sendMessage($this->prefix . "Please enter an entity type.");
                                return true;
                            }
                            if ($name === "") {
                                $name = $sender->getDisplayName();
                            }
                            $types = self::ENTITY_TYPES;
                            $aliases = self::ENTITY_ALIASES;
                            $chosenType = null;
                            foreach ($types as $t) {
                                if (strtolower($type) === strtolower($t)) {
                                    $chosenType = $t;
                                }
                            }
                            if ($chosenType === null) {
                                foreach ($aliases as $alias => $t) {
                                    if (strtolower($type) === strtolower($alias)) {
                                        $chosenType = $t;
                                    }
                                }
                            }
                            if ($chosenType === null) {
                                $sender->sendMessage($this->prefix . "Invalid entity type.");
                                return true;
                            }

                            /** @var class-string $slapperClass */
                            $slapperClass = __NAMESPACE__ . "\\entities\\Slapper$chosenType";
                            Utils::testValidInstance($slapperClass, SlapperInterface::class);

                            $location = $sender->getLocation();
                            if(is_a($slapperClass, SlapperHuman::class, true)){
                                /** @var SlapperHuman $entity */
                                $entity = new $slapperClass($location, $sender->getSkin());
                            }else{
                                /** @var SlapperEntity $entity */
                                $entity = new $slapperClass($location);
                            }
                            $entity->setNameTag($name);
                            $entity->setSlapperVersion($this->getDescription()->getVersion());
                            (new SlapperCreationEvent($entity, $slapperClass, $sender, SlapperCreationEvent::CAUSE_COMMAND))->call();
                            $entity->spawnToAll();
                            $sender->sendMessage($this->prefix . $chosenType . " entity spawned with name " . TextFormat::WHITE . "\"" . TextFormat::BLUE . $name . TextFormat::WHITE . "\"" . TextFormat::GREEN . " and entity ID " . TextFormat::BLUE . $entity->getId());
                            return true;
                        default:
                            $sender->sendMessage($this->prefix . "Unknown command. Type '/slapper help' for help.");
                            return true;
                    }
                    $sender->sendMessage($this->prefix . "This command only works in game.");
                }
        }
        return true;
    }

    /**
     * @param EntityDamageEvent $event
     *
     * @return void
     */
    public function onEntityDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();
        if ($entity instanceof SlapperInterface) {
            $event->cancel();
            if (!$event instanceof EntityDamageByEntityEvent) {
                return;
            }
            $damager = $event->getDamager();
            if (!$damager instanceof Player) {
                return;
            }
            $hitEvent = new SlapperHitEvent($entity, $damager);
            $hitEvent->call();
            if ($hitEvent->isCancelled()) {
                return;
            }
            $damagerName = $damager->getName();
            if (isset($this->hitSessions[$damagerName])) {
                if ($entity instanceof SlapperHuman) {
                    $entity->getInventory()->clearAll();
                }
                $entity->close();
                unset($this->hitSessions[$damagerName]);
                $damager->sendMessage($this->prefix . "Entity removed.");
                return;
            }
            if (isset($this->idSessions[$damagerName])) {
                $damager->sendMessage($this->prefix . "Entity ID: " . $entity->getId());
                unset($this->idSessions[$damagerName]);
                return;
            }

            if (count($commands = $entity->getCommands()) > 0) {
                $server = $this->getServer();
                foreach ($commands as $command) {
                    $server->dispatchCommand($this->commandSender, str_replace("{player}", '"' . $damagerName . '"', $command));
                }
            }
        }
    }

    /**
     * @param EntitySpawnEvent $ev
     *
     * @return void
     */
    public function onEntitySpawn(EntitySpawnEvent $ev): void {
        $entity = $ev->getEntity();
        if ($entity instanceof SlapperEntity || $entity instanceof SlapperHuman) {
            //Plugin not available in poggit
            /*$clearLagg = $this->getServer()->getPluginManager()->getPlugin("ClearLagg");
            if ($clearLagg !== null) {
                $clearLagg->exemptEntity($entity);
            }*/
        }
    }

    /**
     * @param EntityMotionEvent $event
     *
     * @return void
     */
    public function onEntityMotion(EntityMotionEvent $event): void {
        $entity = $event->getEntity();
        if ($entity instanceof SlapperEntity || $entity instanceof SlapperHuman) {
            $event->cancel();
        }
    }
}
