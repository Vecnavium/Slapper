<p align="center">
    <a href="https://github.com/Vecnavium/Slapper"><img src="https://github.com/Vecnavium/Slapper/blob/stable/icon.png"></img></a><br>
    <b>The new home of Slapper, the NPC plugin for PocketMine-MP.</b>
</p>
<p align="center">
Currently maintained by: KressentHosting  

<p align="center">Website: https://kressenthosting.com
	

<p align="center">
    <img alt="GitHubrelease" src="https://img.shields.io/github/v/release/Vecnavium/Slapper?label=release&sort=semver">
      <img alt="Stars" src= "https://img.shields.io/github/stars/Vecnavium/Slapper?style=for-the-badge">
    <img href="https://discord.gg/6M9tGyWPjr"><img src="https://img.shields.io/discord/837701868649709568?label=discord&color=7289DA&logo=discord" alt="Discord" /></a>
</p>

## Addons

Official addons:
- [SlapperRotation](https://github.com/Vecnavium/SlapperRotation)
- [SlapperCooldown](https://github.com/Vecnavium/SlapperCooldown)
- [SlapBack](https://github.com/vecnavium-pm-pl/SlapBack)
- [SlapperCache](https://github.com/Vecnavium/SlapperCache)
- [SlapperPlus](https://github.com/Vecnavium/SlapperPlus)


# Basic documentation

## Commands:

- `/slapper [args...]`
- `/rca [player] [command]` - Run a command as another player! (This can be used to only run the command if the target player has permission.)

## Main Commands:
- **Help:** `/slapper help`
- **Spawn:** `/slapper spawn [type] [name]`
- **Edit:** `/slapper edit [id] [args...]`
- **ID:** `/slapper id`
- **Remove:** `/slapper remove [id]`
- **Version:** `/slapper version`
- **Cancel:** `/slapper cancel`
- **Update All:** `/slapper updateall`

### Edit Command Arguments:
- **Helmet:** `/slapper edit [eid] helmet [id]`
- **Chestplate:** `/slapper edit [eid] chestplate [id]`
- **Leggings:** `/slapper edit [eid] leggings [id]`
- **Boots:** `/slapper edit [eid] boots [id]`
- **Skin:** `/slapper edit [eid] skin`
- **Name:** `/slapper edit [eid] name [name]`
- **Add Command:** `/slapper edit [eid] addcommand [command]`
- **Delete Command:** `/slapper edit [eid] delcommand [command]`
- **List Commands:** `/slapper edit [eid] listcommands`
- **Update:** `/slapper edit [eid] update`
- **Block:** `/slapper edit [eid] block [id]`
- **TPHere:** `/slapper edit [eid] tphere`
- **TPTo:** `/slapper edit [eid] tpto`
- **Menu Name:** `/slapper edit [eid] menuname <name/remove>`
	
	
### Aliases for Edit Command Arguments
- **Helmet:** `helm, helmet, head, hat, cap`
- **Chestplate:** `chest, shirt, chestplate`
- **Leggings:** `pants, legs, leggings`
- **Boots:** `feet, boots, shoes`
- **Item:** `hand, item, holding, arm, held`
- **Skin:** `setskin, changeskin, editskin, skin`
- **Slapper Name:** `name, customname`
- **Menu Name:** `listname, nameonlist, menuname`
- **Name Visiblity:** `namevisible, customnamevisible, tagvisible, name_visible, custom_name_visible, tag_visible`
- **Add Command:** `addc, adduced, add command`
- **Delete Command:** `delc, delcmd, delcommand, remove command`
- **List Commands:** `listcommands, listcmds, listcs`
- **Fix Slapper:** `update, fix, migrate`
- **Block:** `block, tile, blockid, tileid`
- **TP Here:** `teleporthere, tphere, movehere, bringhere`
- **TP To:** `teleportto, tpto, goto, teleport, tp`
