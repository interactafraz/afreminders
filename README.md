# AFreminders

AFreminders is a tool for quickly setting up recurring reminders that can be accessed via a single RSS feed or JSON. It works great with services that allow RSS feeds as triggers to execute actions like [n8n](https://github.com/n8n-io/n8n), [IFTTT](https://ifttt.com/) and [Zapier](https://zapier.com/).

## Possible use cases

* Stay connected with loved ones who live far away by scheduling regular phone calls
* Incorporate mindfulness into your weekly routine by planning spa days at regular intervals
* Create a meal plan to stay organized and make healthier food choices
* Maintain a consistent workout schedule by setting reminders for every second day
* Ensure you never miss taking your medication with automated reminders

## Features

* Self host-able
* Available in German (default language) and English
* Manage reminders via responsive backend interface
* Start/restart/stop reminders via GET parameters
* Add reminders to groups for better organization
* Mark reminders to automatically shift to the next day when missed
* Add attributes to reminders
* Saves data to JSON file

## Prerequisites

* Common web server with PHP support

## Installation

1. Set your dashboard language in inc/globals.php
2. Copy files to web server

## Usage

Access the script via `index.php` to get the RSS feed. To add, remove, start, restart or stop reminders, use the dashboard. Overdue reminders get restarted automatically when the feed is accessed and will be displayed immediately afterwards.

### Dashboard (dashboard.php)

The dashboard lists all existing reminders and allows you to start, restart or stop them. Stopped reminders do not appear in the RSS feed. To control reminders via GET parameters, use `dashboard.php?start=reminder-id`, `dashboard.php?restart=reminder-id` or `dashboard.php?stop=reminder-id`.

To get all reminder data as JSON, use `dashboard.php?data=all`.

---

Although the `start` and `restart` actions sound similar they behave a little different.
* `start` is only possible when a reminder interval isn't running at all. Use this action to initialize the interval. The first day of the interval will match the day it was started.
* `restart` is only possible when a reminder interval is already running and at least 1 day has passed. Use this action to reset the interval. The first day of the new interval will be the day it was restarted, not the initialization day.

> Note: When using GET parameters either `start` or `restart` can be used to start/reset an interval.

---

To add/remove reminders, use the `Edit list` button, which opens a form with the following fields:

**ID**: A unique ID for your reminder, used for the timestamp filename and as the item description in the RSS feed. Only regular english letters, numbers and hyphens recommended.

**Title**: The RSS item title.

**Interval**: Days between reminders

* *7* would make a reminder appear every week on the same weekday (perfect for *Thirsty Thursday*)
* *1* would make a reminder appear daily

**Group**: An advanced feature for users with many reminders. Use it to handle reminders with different priorities efficiently.

* All: For reminders with both low and high priority.
* Essential: For reminders with high priority at home, such as trash routines.
* Sabbatical: For reminders with high priority on long trips, such as health routines or important daily tasks.

Groups can be useful for the control system that gets triggered by the RSS feed. You could, for example, set up a *Global Timeout Status* variable that helps to exclude RSS items based on their reminder group when you're on vacation at home or traveling around the world.

> Note: Group values do not affect the visibility of RSS feed items.

**Shiftable tag**: An advanced feature for users who need missed reminders to automatically shift to the next day. Use it to tell your control system that a reminder should be restarted the next day (via GET parameters) when your control functions register that you missed it. Two settings are available.

* *Yes* would tell your control system `Reset the interval counter and set the next day as the first day of a new interval.`
* *No* would tell your control system `If I forget this, keep reminding me without interrupting the current interval.`. This setting is great for *Taco Tuesdays*!

> Note: This value only affects the JSON file. To take advantage of this feature, you need to implement custom conditions in your control system. Otherwise this setting won't change anything.

---

**Attributes**: A hidden feature that can be used to add custom data to reminders. Possible use cases include attaching a status/label to specific reminders or mark certain ones as *processed*. 

To set an attribute, use `dashboard.php?id=reminder-id&attribute=attribute-data`. If an attribute was already set before it will be overwritten.

The data will be saved to the `timestamps` folder in a `reminder-id_attributes.txt` file.

> Note: Attributes get reset when a reminder is started, restarted or stopped.

## Notes

* If you plan to handle sensitive data with this tool on a publicly accessible server, consider hardening your system using at least htaccess restrictions.
* AFreminders comes from *AFRAZ* and *reminders*.

## License

[MIT](https://github.com/interactafraz/afreminders/blob/main/LICENSE.txt)