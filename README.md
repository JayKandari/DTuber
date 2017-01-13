# DTuber
Drupal 8 module for uploading videos to youtube

> Current Status: **Under Development**

# Dependencies

This module requires [Google Client API](https://github.com/google/google-api-php-client)

Install google-api-php-client by following command

`composer require google/apiclient:^2.0`

# Module Usage:
1. Install Google Client API Library via composer.
2. Clone/Download this repo to **/modules/dtuber** directory
3. Enable DTuber module (via drush or by Drupal's Extend page)
4. Create an application at http://console.developers.google.com. Set Client ID, Client Secret & Redirect uri. **Enable YouTube Data API**.
5. navigate to Dtuber Config page : **/admin/config/media/dtuber_config**
6. Make sure Redirect uri matches as per given in description of DTuber Config page.
7. Then Click link which says 'Click here to Authorize'. That will ask for your youtube channel's permission.
8. You are ready to go. Goto this test form (**/dtuber/testform**) to test DTuber. Check your YouTube Channel for latest updated Video.
9. Alternatively, An Extra CCK Field(**Dtuber - Upload to YouTube**) is added under "Media" category. Add to any of your Content Type.
10. When creating a new content. Add a video, and click save.
11. Video will get uploaded to your Channel.
12. Make sure you enter google credentials to be able to use this module effectively. Enjoy !!

# Bugs/Features/Warning/Contrib:
This module is under development. Do let me ([Tweet](http://twitter.com/JayKandari)) know for any Bugs/Feature/Contribs/etc... :)

# References
* https://capgemini.github.io/drupal/writing-custom-fields-in-drupal-8/
* http://drupal.stackexchange.com/questions/188924/how-to-embed-drupal-content-in-other-sites-remove-x-frame-options-sameorigin/188925
* https://github.com/google/google-api-php-client
* https://dev.acquia.com/blog/coding-drupal-8/generating-urls-and-redirects-in-drupal-8/22/02/2016/9626
* https://www.drupal.org/node/2491981
* https://developers.google.com/youtube/v3/guides/auth/server-side-web-apps
* https://drupalize.me/blog/201502/responding-events-drupal-8
* https://www.drupal.org/node/2064123
* https://www.computerminds.co.uk/drupal-code/drupal-8-creating-custom-field-part-1-field-type
* http://stackoverflow.com/questions/9241213/how-to-refresh-token-with-google-api-client
* https://www.drupal.org/node/1985716
* https://www.drupal.org/node/1882526
* https://www.webwash.net/drupal/tutorials/upgrading-code-snippets-module-drupal-8-creating-custom-field
* And Many more...
