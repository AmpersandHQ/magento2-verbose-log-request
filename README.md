# magento2-verbose-log-request

[![Build Status](https://travis-ci.com/AmpersandHQ/magento2-verbose-log-request.svg?token=4DzjEueYNQwZuk3ywXjG&branch=master)](https://app.travis-ci.com/AmpersandHQ/magento2-verbose-log-request)

## Summary

Dynamically change the [log level per request](https://devopedia.org/log-level-per-request) to `DEBUG`. This enables database, debug log, and verbose logging for a specifically defined request.

Pass in a `X-Verbose-Log` header and Magento will activate the kind of logging you usually only have in developer mode for that request. 

This means you can get verbose information when you are debugging something on production without having to switch on these settings forcefully. This is beneficial as on high traffic sites this can produce a lot of log data and narrowing in for what you are interested in can be difficult otherwise.

It is recommended that you also install [ampersand/magento2-log-correlation-id](https://github.com/AmpersandHQ/magento2-log-correlation-id/) to help correlate your log entries together.

Compatible with Magento 2.4.1 and higher.

## Features

- Database query (and stack trace) logging will be enabled and output to `./var/log/verbose_db.log`
  - https://experienceleague.adobe.com/docs/commerce-operations/configuration-guide/cli/enable-logging.html#database-logging
  - See `src/Logger/DB/LoggerProxy.php`
- Magento `debug` logging usually requires that you set a flag and flush a cache, we can pass the flag to activate it for your specific requests without these steps
  - https://experienceleague.adobe.com/docs/commerce-operations/configuration-guide/cli/enable-logging.html#debug-logging
  - See `src/Logger/Handler/Debug.php`
- Bundled in this module is a cache decorator which will add to the vanilla offering of logging `cache_invalidate` with `cache_load` and `cache_save` information.
  - This uses a virtual type `Ampersand\VerboseLogRequest\Logger\VerboseDebugLogger` which calls to `->debug()` on the `Psr\Log\LoggerInterface`.
  - See `src/CacheDecorator/VerboseLogger.php`
- Set redis session log level to debug mode (level 7)
  - Allows you to see redis lock acquisitions, lock waits, zombie processes, etc
  - https://github.com/colinmollenhour/Cm_RedisSession#configuration-example

## Example Usage

On the system you want to debug run the following command to get the current key

```
$ php bin/magento ampersand:verbose-log-request:get-key
The current key is:               d07c0ee76154d48c2974516ef22c1ec0
The current key will expire at:   2022-10-25 09:00:00
```

Make a request to your desired page with an `X-Verbose-Log` header set to that value

```bash
curl -H "X-Verbose-Log: d07c0ee76154d48c2974516ef22c1ec0" https://example.com/your-page/
```

or 

```
X_VERBOSE_LOG=d07c0ee76154d48c2974516ef22c1ec0 php bin/magento some:command:here
```

See all your verbose log files
```shell
 12M var/log/debug.log
3.1M var/log/verbose_db.log
4.0K var/log/system.log
4.0K var/log/support_report.log
```

If you want to do more complex interactions you could use something like [modheader](https://chrome.google.com/webstore/detail/modheader/idgpnmonknjnojddfkpgkljpfnnfcklj?hl=en) to set this value for a series of requests, be aware that will output a LOT of debug data. 

## Installation

Composer require the module.

```
composer require ampersand/magento2-verbose-log-request
```

Run module installation, this will generate your `ampersand/verbose_log_request/key` in `app/etc/config.php`, commit this change.
```
php bin/magento setup:upgrade
```

Update your `.gitignore` to ignore 

```
app/etc/di.xml_ampersand_magento2_verbose_log_request/di.xml
```

## Security considerations

As all we are doing is writing to the log files the biggest "risk" is to your disk space. 

However as you need to know the key to trigger the logging it can be locked down to your developers and won't be accessible to the outside world unless they already have access to your file system.

## A log level below "debug", the VerboseDebugLogger

By default magento has `debug` level logging enabled on `developer` mode and it may even be activated on some production environments. As we want to log `cache_load` and `cache_save` information this would rapidly fill up your log files on either your developer machine, or those production enivornments with lots of unnecessary data. 

To ensure we only trigger these _extra verbose_ debug level logging when the `X-Verbose-Log` request is flagged, we have a virtual type that you can inject into your classes. 

`Ampersand\VerboseLogRequest\Logger\VerboseDebugLogger` will write to the `./var/log/debug.log` file the same as the standard calls to `->debug()`, but they will only write when flagged to in the request.

In this manner we can spoof in a sort of log level below `DEBUG` as defined in [RFC 5424](https://www.rfc-editor.org/rfc/rfc5424). It is still a debug log, but it is only triggered when specifically requested and is therefore a bit separate from standard debug logs.

You can tell your class to use this kind of debug logging by injecting it in place of the standard `\Psr\Log\LoggerInterface` by defining a `di.xml` like so

```xml
<type name="Namespace\Module\Your\Class\Here">
    <arguments>
        <argument name="logger" xsi:type="object">Ampersand\VerboseLogRequest\Logger\VerboseDebugLogger</argument>
    </arguments>
</type>
```

### It's great for adding hotfix logging

We all hope that it is never necessary but if you ever feel the need to add some hotfix logging onto an environment you can improve it by using the `VerboseDebugLogger`

Inject that dependency (as described above) and you can put debug statements all through your application that will only trigger when you request it on the problematic environment.
