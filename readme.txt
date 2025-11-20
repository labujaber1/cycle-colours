=== Cycle Colour ===
Contributors: Lawrence Abu-Jaber
Donate link: https://github.com/labujaber1
Tags: colour, palette, cycle, background, styles
Requires at least: 5.0
Tested up to: 6.3
Requires PHP: 8.4.4
Stable tag: 1.0.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Can't decide between different colour palettes or for a specific div?
Why not alternate between them periodically and manage them from the dashboard? 

== Installation ==

Standard Installation
1. Upload the plugin files to the `/wp-content/plugins/cycle-colour` directory, or install 
the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to 'Cycle Colours admin' in your WordPress dashboard and have a play.

== Further description ==

Cycle Colours is a WordPress plugin that allows administrators to manage and set a schedule to cycle through 
selected style files containing colour palettes, or colours for any div with a class or id. All settings and controls are 
available from a dedicated admin page — no block insertion or frontend editing required. A testing schedule of
1 minute is available but not recommended for live sites. Changes are seen on a page load as the styles are merged
with the theme.json and the individual div styles are added as a single inline css per interval chosen. 

**Features:**
* Manage colour palette and div selections and cycling intervals from the dashboard.
* Include multiple div classes or ids.
* Can have the style files and specific divs change together.
* A simple custom admin page for easy configuration.
* No block editor integration required.
* No dependencies, no classes, its designed to be light weight.
* Intended for block based themes where palettes and styles are configured in .json files in a styles directory. Can also be used 
in classic themes by selecting specific div classes or ids.
* No data of any kind is actively collected from you from the plugin.

== Some questions and answers ==

= Does this plugin add a block to the editor? =
No. All functionality is managed from the admin dashboard.

= Can I schedule colour changes? =
Yes, you can select an interval from a dropdown list for cycling through the selected colours
Individual div changes generate separate schedule events grouped by interval giving you more flexibility and choice.
It is scheduled as soon as it is saved. A start/end time and date option will follow in a later version.

= Can I select an Id to change?
Yes, you will have to add the id name in the wp editor first which may look something like #idname.wp-block-post-title
or #idname.wp-block-button on the frontend so this full reference, with prefixed #, is required to work not just the id name.
Using just the class will change all instances of the class.

= Can I create a style file or colour palette?
Not yet but it is under consideration for future development. Currently the plugin searches for existing style (.json) files 
in the style folder and sub directories so they have to already exist. Just add a new .json file to the style folder in the 
parent or child theme with with your chosen colour palettes or styles in the same format as the theme.json and the plugin will find it.

= Can I have styles and multiple divs together changing at different times?
Yes, you can set as many specific divs as you want. You can have the site change its palette each week alternating 
up to 4 styles while having the header change its background colour each day and its font colour each month as an example. 

= Can I set a date or time to stop the changes?
Not yet, the schedule events will keep going until you manually change the interval time in the edit schedule section. 
Setting the interval to 'disabled' will stop the schedule and remove the css inline data. The schedule will remain visible in the 
edit schedule section to change the interval to a positive time. If the class or id is deleted the schedule will also 
be deleted.


= Who would use this plugin?
Anyone who would like to add colour variety to their website design in an automated way. Regular visiters will appreciate 
the site changing colour and font changing to add another element of interest. If you cannot decide on a light shade of 
blue or red for the content container background colour then alternate them indefinitely.

== Note for developers and administrators ==
The point of this plugin is to alternate a websites colour scheme in an automated way so the user doesn't have to do 
it manually. 'Colour palettes' is used in code to refer to the themes .json files in the style directory and colour 
subdirectory. The majority of the themes researched contain only the colour palette but some files do contain styles, i.e 
2024/25, and they do not always follow the WordPress block preference of being contained in a colour directory. 
Therefore the plugin searches the styles directory and sub directories for all .json files and removes duplicate names
so they are displayed for the user to select. All the content of the selected .json file are merged with the theme.json.
To create your own .json file check the theme.json first to identify what name format is used such as accent-1 or base-1 
or something totally unique to the theme. The term 'div' refers to any html tag with a class or id and is used throughout 
the code and comments. The plugin does not distinguish between html tags as the inline css does not need it to work. 
Changing a container, dropdown list, input field will work providing the class or id entered is correct for the frontend.
The interval times are 1 minute (for testing), 1 hour, daily, weekly, and monthly. When testing allow for 1-1.5 minute 
before refreshing the page preview as sometimes it is not exactly a minute between scheduled events even though wordpress 
scheduling functions are used.

== Screenshots ==

1. Cycle styles settings. 
2. Cycle specific divs settings.
3. Manage divs section.
4. Edit schedules section.


== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
First release.

== License ==

GPL v2 or later license. See LICENSE file for details.