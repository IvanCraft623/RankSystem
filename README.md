<div align="center">
  <h1> 👑 RankSystem 🔧</h1>
  <p>An amazing Rank and Permissions Manager</p>
</div>

## Description:
An amazing Rank and Permissions Manager. The best ranks manager for PocketMine-MP.
## For Developers
Examples and tutorials can be found on the [RankSystem Wiki](https://github.com/IvanCraft623/RankSystem/wiki).

# Features

- Multiple Ranks per user support
- Data migrator from other ranks/groups plugins (PurePerms)
- Multi-rank inheritance system to allow you to inherit rank permissions
- Temp-ranks System
- Temp-ranks System
- SQLite3 Provider Support
- MySQL Provider Support
- Easy Rank Creation / Edit System
- Ranks inheritance system to allow you to inherit rank permissions
- Permissions per User
- Profile System
- Provides simple and flexible API for plugin developers
- ScoreHud Integration
- And more...!

# Commands
Command | Description | Permission
--- | --- | ---
`/ranks create` | Create a Rank | ranksystem.commands
`/ranks delete <rank>` | Delete a Rank. | ranksystem.commands
`/ranks edit <rank>` | Edit a Rank | ranksystem.commands
`/ranks list` | Show all ranks list. | ranksystem.commands
`/ranks set <user> <rank> [expTime]` | Set a Rank to a User | ranksystem.commands
`/ranks remove <user> <rank>` | Remove a Rank of a User | ranksystem.commands
`/ranks setperm <user> <permission>` | Set a Permission to a User | ranksystem.commands
`/ranks removeperm <user> <permission>` | Remove a Permission of a User | ranksystem.commands
`/ranks perms <plugin>` | Show a list of all plugin permissions | None
`/ranks credits` | Show RankSystem Credits | None

# ScoreHud Integration
This plugin supports ScoreHud plugin.
Tag | Description
--- | ---
`{ranksystem.ranks}` | The ranks of the player
`{ranksystem.highest_rank}` | The player's highest rank
`{ranksystem.nametag}` | NameTag assigned by RankSystem to the player


# Project information
Version | Pocketmine API | PHP | Status
--- | --- | --- | ---
1.0.0 | [PM4](https://github.com/pmmp/PocketMine-MP/tree/stable) & [PM3](https://github.com/pmmp/PocketMine-MP/tree/legacy/pm3) | 8 | Functional
