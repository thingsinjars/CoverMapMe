CoverMap Me
===

Set a [Nokia Map](http://maps.nokia.com) as your Facebook Timeline cover image.

This is the source for the website [covermap.me](http://covermap.me/). We first use the Facebook PHP SDK to log the user in. Then there's some JS to let the user position an interactive map (with an integrated search) created with the [Nokia Maps JS API](http://api.maps.nokia.com/en/maps/intro.html) and some PHP to grab the same map area as a static image using the [Nokia RESTful Maps API](http://api.maps.nokia.com/en/restmaps/overview.html).

After that, the image is downloaded, a watermark is added using GD then it's uploaded to Facebook.

Finally, the user is redirected to a URL on Facebook where they can confirm the change in cover image.

For a better description of the interesting bits, [read this blog post](http://thingsinjars.com/post/463/covermap---nokia-maps-on-facebook/)

===

Requirements
---

### Server
The only prerequisite on the server side is to have GD enabled. 

### Facebook App
Visit the Facebook Developers site to create an app:
https://developers.facebook.com/apps/

Everything under `/src/` is the Facebook PHP SDK. You should use the most up-to-date version.

### Nokia App
Visit the Nokia Developers site to create an app:
http://api.developer.nokia.com/

In `config.php`, enter your Facebook and Nokia App IDs and secrets.

Disclaimer
---
This project's here mostly so people can grab whatever bits of code they want out of it. I'm not a particularly great PHP developer so it's a bit scrappy and old-fashioned. It still works, however.

The most useful sections are probably the bits that let you go between an interactive map and a static map.
