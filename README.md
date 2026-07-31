<div align="center">
  <h1>👑 RankSystem 🔧</h1>
  <p>The most powerful and flexible Rank & Permissions Manager for PocketMine-MP</p>

  [![CI](https://img.shields.io/github/actions/workflow/status/IvanCraft623/RankSystem/phpstan.yml?label=CI&style=flat&logo=github)](https://github.com/IvanCraft623/RankSystem/actions/workflows/phpstan.yml)
  [![Poggit Downloads](https://poggit.pmmp.io/shield.dl.total/RankSystem?style=flat)](https://poggit.pmmp.io/p/RankSystem)
  [![GitHub Downloads](https://img.shields.io/github/downloads/IvanCraft623/RankSystem/total?style=flat&label=github+downloads&logo=github&logoColor=white)](https://github.com/IvanCraft623/RankSystem/releases)
  [![bStats Servers](https://img.shields.io/bstats/servers/33024?style=flat&logo=googleanalytics&logoColor=white)](https://bstats.org/plugin/pocketmine/RankSystem/33024)
  [![bStats Players](https://img.shields.io/bstats/players/33024?style=flat&logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDE2IDE2Ij48cGF0aCBmaWxsPSJ3aGl0ZSIgZD0iTTIgNS41YTMuNSAzLjUgMCAxIDEgNS44OTggMi41NDkgNS41MDggNS41MDggMCAwIDEgMy4wMzQgNC4wODQuNzUuNzUgMCAxIDEtMS40ODIuMjM1IDQgNCAwIDAgMC03LjkgMCAuNzUuNzUgMCAwIDEtMS40ODItLjIzNkE1LjUwNyA1LjUwNyAwIDAgMSAzLjEwMiA4LjA1IDMuNDkzIDMuNDkzIDAgMCAxIDIgNS41Wk0xMSA0YTMuMDAxIDMuMDAxIDAgMCAxIDIuMjIgNS4wMTggNS4wMSA1LjAxIDAgMCAxIDIuNTYgMy4wMTIuNzQ5Ljc0OSAwIDAgMS0uODg1Ljk1NC43NTIuNzUyIDAgMCAxLS41NDktLjUxNCAzLjUwNyAzLjUwNyAwIDAgMC0yLjUyMi0yLjM3Mi43NS43NSAwIDAgMS0uNTc0LS43M3YtLjM1MmEuNzUuNzUgMCAwIDEgLjQxNi0uNjcyQTEuNSAxLjUgMCAwIDAgMTEgNS41Ljc1Ljc1IDAgMCAxIDExIDRabS01LjUtLjVhMiAyIDAgMSAwLS4wMDEgMy45OTlBMiAyIDAgMCAwIDUuNSAzLjVaIi8+PC9zdmc+)](https://bstats.org/plugin/pocketmine/RankSystem/33024)
  [![License](https://img.shields.io/github/license/IvanCraft623/RankSystem?style=flat&logo=opensourceinitiative&logoColor=white)](LICENSE)
</div>

---

## 📃 Description

**RankSystem** is a permissions manager for [PocketMine-MP](https://github.com/pmmp/PocketMine-MP). It simplifies complex hierarchy management with a system for multiple ranks, temporal permissions, and a user-friendly forms interface.

---

## ✨ Features

* 🌍 **Global:** Full Multi-language support.
* 👥 **Multi-Rank:** Support for multiple ranks per user simultaneously.
* ⏳ **Temporary System:** Set expiration times for both ranks and specific permissions.
* 💾 **Data Support:** Native support for **SQLite3** and **MySQL** providers.
* 🔄 **Easy Migration:** Built-in migrator for existing data (e.g., PurePerms).
* 🛠️ **UI Driven:** Intuitive Forms for management without complex commands.
* 🧬 **Inheritance:** Advanced rank inheritance system to simplify permission management.
* 💻 **API Ready:** Flexible API designed for plugin developers.
* 📊 **Integrations:** Full ScoreHud integration to display ranks on scoreboards.

---

## 📥 Download

<div align="center">

[![Stable Release](https://img.shields.io/github/v/release/IvanCraft623/RankSystem?label=Stable+Release&style=for-the-badge&logo=github&logoColor=white&color=2292ee)](https://github.com/IvanCraft623/RankSystem/releases/latest/download/RankSystem.phar)
[![Nightly Build](https://img.shields.io/badge/dynamic/yaml?url=https://raw.githubusercontent.com/IvanCraft623/RankSystem/main/plugin.yml&query=$.version&label=Nightly+Build&prefix=v&suffix=%2Bdev&color=blueviolet&style=for-the-badge&logo=github&logoColor=white)](https://github.com/IvanCraft623/RankSystem/releases/download/nightly/RankSystem.phar)

*Stable recommended for production · Nightly always up to date from the latest commit*

<sub>Previous releases can also be found on <a href="https://poggit.pmmp.io/p/RankSystem">Poggit</a> while the service is still running.</sub>

</div>

---

## 🤖 Commands

| Command | Description | Permission |
| :--- | :--- | :--- |
| `/ranks manage` | Open the management UI | `ranksystem.command.manage` |
| `/ranks create` | Create a new Rank | `ranksystem.command.create` |
| `/ranks delete <rank>` | Delete a Rank | `ranksystem.command.delete` |
| `/ranks edit <rank>` | Edit a Rank's properties | `ranksystem.command.edit` |
| `/ranks list` | List all available ranks | `ranksystem.command.list` |
| `/ranks setrank <user> <rank> [time]` | Set a Rank to a User | `ranksystem.command.setrank` |
| `/ranks removerank <user> <rank>` | Remove a Rank from a User | `ranksystem.command.removerank` |
| `/ranks setpermission <user> <perm> [time]` | Set a Permission to a User | `ranksystem.command.setpermission` |
| `/ranks removepermission <user> <perm>` | Remove a User permission | `ranksystem.command.removepermission` |
| `/ranks permissions <plugin>` | List all plugin permissions | `ranksystem.command.permissions` |
| `/ranks rankinfo <rank>` | Show info about a rank | `ranksystem.command.rankinfo` |
| `/ranks userinfo <user>` | Show info about a user | `ranksystem.command.userinfo` |

> [!TIP]
> You can use `/ranks` as a shortcut for the `/ranksystem` command.

---

## 📊 ScoreHud Integration

Use these tags to display information in [ScoreHud](https://poggit.pmmp.io/p/ScoreHud):

| Tag | Description |
| :--- | :--- |
| `{ranksystem.ranks}` | Shows all player's ranks. |
| `{ranksystem.highest_rank}` | Shows the player's highest rank. |
| `{ranksystem.nametag}` | Shows the RankSystem assigned NameTag. |

---

## 📋 FAQ

<details>
<summary><b>Why is the previous rank not removed when I use /ranks setrank?</b></summary>
RankSystem is a <b>multi-rank</b> system, meaning ranks are stackable. If you want to remove a specific rank from a player, you must use <code>/ranks removerank</code>.
</details>

<details>
<summary><b>Will you add support for JSON or YAML data storage?</b></summary>
<b>No. Never.</b> These formats are not designed to function as databases. As the player base grows, they become extremely slow and can cause server lag. RankSystem uses high-performance storage solutions to ensure stability.
</details>

<details>
<summary><b>Can I block users from using formatting codes (§*) in chat?</b></summary>
This is currently <b>outside the scope</b> of this plugin. While it's not difficult to implement, it might be added as a configurable option in a future update.
</details>

<details>
<summary><b>How does the 'time' argument work in /setrank or /setpermission?</b></summary>
If no time is specified, the rank/permission will be permanent. The format used is:
<ul>
  <li><code>y</code> = year, <code>M</code> = month, <code>w</code> = week, <code>d</code> = day, <code>h</code> = hour, <code>m</code> = minute.</li>
</ul>
<b>Examples:</b>
<ul>
  <li><code>1y3M</code>: One year and three months (same as 15M).</li>
  <li><code>1w2d12h</code>: One week, two days, and twelve hours (same as 9d12h).</li>
</ul>
</details>

<details>
<summary><b>How does rank hierarchy work?</b></summary>
The hierarchy is determined by the order of the ranks in the <code>config.yml</code> file under the <code>Hierarchy</code> section. The rank at the <b>top</b> of the list has the highest priority, while the one at the <b>bottom</b> is the lowest. If a rank is not listed there, it will automatically be assigned the lowest possible position.

This hierarchy is used to determine which rank's prefix or nametag should be displayed first. It is especially important when using placeholders like:
<ul>
  <li><code>{nametag_highest-rank_prefix}</code></li>
  <li><code>{chat_highest-rank_prefix}</code></li>
</ul>
These will automatically display the prefix of the player's most powerful rank according to your hierarchy list.
</details>

<details>
<summary><b>How can I see all permissions available for a specific plugin or PocketMine-MP?</b></summary>
RankSystem includes a powerful built-in tool to explore permissions. You can use the command:
<br>
<code>/ranks permissions &lt;pluginName&gt; [page]</code>
<br><br>
This will list all permissions registered by that specific plugin. You can also use <code>pocketmine</code> as the argument to see all core permissions!
<br><i>Note: This works as long as the plugin has correctly registered its permissions in its <code>plugin.yml</code> or via code.</i>
</details>

<details>
<summary><b>I found a bug or my server crashed! What should I do?</b></summary>
Please <b>report the issue on GitHub</b>. Make sure to include the crash dump and steps to reproduce the error so I can fix it as soon as possible.
</details>

<details>
<summary><b>I need help configuring the plugin. Where can I find support?</b></summary>
Don't hesitate to <b>ping me on the official PMMP Discord</b>. I'm usually around to help with configuration questions.
</details>

---

## 🛠 For Developers

Find examples, API methods, and tutorials on the **[RankSystem Wiki](https://github.com/IvanCraft623/RankSystem/wiki)**.

---

## 💖 Support the Project

RankSystem is and will always be **free and open-source**. If you enjoy the plugin or it helps your server, consider supporting future development — it goes a long way!

<div align="center">

[![Donate](https://img.shields.io/badge/Donate-Support_Me-ff69b4?style=for-the-badge&logo=ilovepdf&logoColor=white)](https://donate.endergames.org/IvanCraft623)

</div>
