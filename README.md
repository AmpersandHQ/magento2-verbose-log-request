# magento2-verbose-log-request

[![Build Status](https://travis-ci.com/AmpersandHQ/magento2-verbose-log-request.svg?token=4DzjEueYNQwZuk3ywXjG&branch=master)](https://app.travis-ci.com/AmpersandHQ/magento2-verbose-log-request)

Enable database, debug log, and verbose logging for a specifically defined request.

Pass in a `X-Verbose-Log` header and Magento will activate the kind of logging you usually only have in developer mode for that request. 

This means you can get verbose information when you are debugging something on production without having to switch on these settings forcefully. This is beneficial as on high traffic sites this can produce a lot of log data and narrowing in for what you are interested in can be difficult otherwise.

Features
- Database query (and stack trace) logging will be enabled and output to `./var/log/verbose_db.log`
  - https://experienceleague.adobe.com/docs/commerce-operations/configuration-guide/cli/enable-logging.html#database-logging
  - See `src/Logger/DB/LoggerProxy.php`
- Magento `debug` logging usually requires that you set a flag and flush a cache, we can pass the flag to activate it for your specific requests without these steps
  - https://experienceleague.adobe.com/docs/commerce-operations/configuration-guide/cli/enable-logging.html#debug-logging
  - See `src/Logger/Handler/Debug.php`
- Bundled in this module is a cache decorator which will add to the vanilla offering of logging `cache_invalidate` with `cache_load` and `cache_save` information.
  - This uses a virtual type `Ampersand\VerboseLogRequest\Logger\VerboseDebugLogger` which calls to `->debug()` on the `Psr\Log\LoggerInterface`.
  - See `src/CacheDecorator/VerboseLogger.php`

## Example Usage

On the system you want to debug run the following command to get the key for today
```
$ php bin/magento ampersand:verbose-log-request:get-key
Todays key is : d07c0ee76154d48c2974516ef22c1ec0
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

## VerboseDebugLogger

On some magento systems `debug` level logging may already be activated on some environments. 

In this module I needed to also log `cache_load` and `cache_save` information to `debug.log` when the `X-Verbose-Log` flag is set, but there is no `->extraDebug` or `->verbose` function on `Psr\Log\LoggerInterface`. 

I did not want to put this information directly into the main `debug` funtionality as on systems with this flag enabled they would get a LOT of extra log data every request, which goes against the idea of this module to only trigger this verbose information when it is needed.

To handle this there is a virtual type `Ampersand\VerboseLogRequest\Logger\VerboseDebugLogger` which can be injected into the classes you want, and the `->debug` calls for that logger interface will only trigger when the `ampersand/verbose_log_request/key` has been set for the request. 

This allows us to do extra verbose `->debug` calls on specific classes, without flooding the log file for environments which already have the vanilla magento debug logging functionality enabled.

## Security considerations

As all we are doing is writing to the log files the biggest "risk" is to your disk space. 

However as you need to know the key to trigger the logging it can be locked down to your developers and won't be accessible to the outside world unless they already have access to your file system.
