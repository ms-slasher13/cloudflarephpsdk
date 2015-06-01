<?php
/**
 * @file
 * Implementation for Zone class.
 */

namespace CloudFlarePhpSdk\ApiTypes\Zone;
use CloudFlarePhpSdk\ApiTypes\CloudFlareApiResponse;

/**
 * The zone class stores settings and parameters associated with zones.
 *
 * The object can parse all the settings from a zone and store their local
 * changes.  It can also return settings a a serialized array that the API
 * can recognise.
 *
 * The zone class does not know anything about the API.
 * Changes made to the zone class must be written back to CloudFlare using the
 * API.
 */
class ZoneSettings {
  /*
   * @var string
   * The zone's zoneId.
   */
  private $id;

  /*
   * @var array
   * Typed ZoneSetting objects.
   */
  private $settings;

  // Zone settings.
  const SETTING_ADVANCED_DDOS = 'advanced_ddos';
  const SETTING_ALWAYS_ONLINE = 'always_online';
  const SETTING_BROWSER_CACHE_TTL = 'browser_cache_ttl';
  const SETTING_BROWSER_CHECK = 'browser_check';
  const SETTING_CACHE_LEVEL = 'cache_level';
  const SETTING_CHALLENGE_TTL = 'challenge_ttl';
  const SETTING_DEVELOPMENT_MODE = 'development_mode';
  const SETTING_EDGE_CACHE_TTL = 'edge_cache_ttl';
  const SETTING_EMAIL_OBFUSCATION = 'email_obfuscation';
  const SETTING_HOTLINK_OBFUSCATION = 'hotlink_protection';
  const SETTING_IP_GEOLOCATION = 'ip_geolocation';
  const SETTING_IPV6 = 'ipv6';
  const SETTING_MAX_UPLOAD = 'max_upload';
  const SETTING_MINIFY = 'minify';
  const SETTING_MINIFY_CSS = 'css';
  const SETTING_MINIFY_JS = 'js';
  const SETTING_MINIFY_HTML = 'html';
  const SETTING_MIRAGE = 'mirage';
  const SETTING_MOBILE_REDIRECT = 'mobile_redirect';
  const SETTING_MOBILE_REDIRECT_ENABLED = 'enabled';
  const SETTING_MOBILE_REDIRECT_MOBILE_SUBDOMAIN = 'mobile_subdomain';
  const SETTING_MOBILE_REDIRECT_STRIP_URI = 'strip_uri';
  const SETTING_POLISH = 'polish';
  const SETTING_PSEUDO_IPV4 = 'pseudo_ipv4';
  const SETTING_ROCKET_LOADER = 'rocket_loader';
  const SETTING_SECURITY_HEADER = 'security_header';
  const SETTING_SECURITY_LEVEL = 'security_level';
  const SETTING_SERVER_SIDE_EXCLUDE = 'server_side_exclude';
  const SETTING_SSL = 'ssl';
  const SETTING_TLS_CLIENT_AUTH = 'tls_client_auth';
  const SETTING_WAF = 'waf';

  /*
   * @var array
   * Listing of the settings which are booleans.
   */
  const BOOLEAN_SETTINGS = [
    self::SETTING_ADVANCED_DDOS,
    self::SETTING_ALWAYS_ONLINE,
    self::SETTING_BROWSER_CHECK,
    self::SETTING_DEVELOPMENT_MODE,
    self::SETTING_EMAIL_OBFUSCATION,
    self::SETTING_HOTLINK_OBFUSCATION,
    self::SETTING_IP_GEOLOCATION,
    self::SETTING_IPV6,
    self::SETTING_MIRAGE,
    self::SETTING_PSEUDO_IPV4,
    self::SETTING_ROCKET_LOADER,
    self::SETTING_SERVER_SIDE_EXCLUDE,
    self::SETTING_TLS_CLIENT_AUTH,
    self::SETTING_WAF,
  ];

  /*
   * @var array
   * Listing of the settings which are integers.
   */
  const INTEGER_SETTINGS = [
    self::SETTING_BROWSER_CACHE_TTL,
    self::SETTING_CHALLENGE_TTL,
    self::SETTING_EDGE_CACHE_TTL,
    self::SETTING_MAX_UPLOAD
  ];

  // Zone cache levels.
  const CACHE_AGGRESSIVE = 'aggressive';
  const CACHE_BASIC = 'basic';
  const CACHE_SIMPLIFIED = 'simplified';

  // Polish settings.
  const POLISH_OFF = 'off';
  const POLISH_LOSSLESS = 'lossless';
  const POLISH_LOSSY = 'lossy';

  const SETTING_WRAPPER_ID = 'id';
  const SETTING_WRAPPER_MODIFIED_ON = 'modified_on';
  const SETTING_WRAPPER_EDITABLE = 'editable';
  const SETTING_WRAPPER_VALUE = 'value';

  /**
   * Constructor for ZoneSettings.
   *
   * @param string $zone_id
   *   The zone's zoneId.
   * @param \CloudFlarePhpSdk\ApiTypes\CloudFlareApiResponse $query_results
   *   The results from an API Zone Settings Query.
   */
  public function __construct($zone_id, CloudFlareApiResponse $query_results) {
    $this->id = $zone_id;

    foreach ($query_results->getResult() as $raw_setting) {
      $setting_name = $raw_setting[self::SETTING_WRAPPER_ID];
      $modified_time = $raw_setting[self::SETTING_WRAPPER_MODIFIED_ON];
      $editable = $raw_setting[self::SETTING_WRAPPER_EDITABLE];
      $value = $raw_setting[self::SETTING_WRAPPER_VALUE];

      // Parse the boolean values into ZoneSettingBools.
      if (in_array($setting_name, self::BOOLEAN_SETTINGS)) {
        $this->settings[$setting_name] = new ZoneSettingBool($value, $setting_name, $editable, $modified_time);
      }

      // Parse the integer values in to ZoneSettingInts.
      elseif (in_array($setting_name, self::INTEGER_SETTINGS)) {
        $this->settings[$setting_name] = new ZoneSettingInt($value, $setting_name, $editable, $modified_time);
      }

      // The remaining types are specific one offs that have additional logic
      // which cannot be accommodated by ZoneSettingBool or ZoneSettingInt.
      else {
        switch ($setting_name) {
          case self::SETTING_MINIFY:
            $css = $value[self::SETTING_MINIFY_CSS];
            $html = $value[self::SETTING_MINIFY_HTML];
            $js = $value[self::SETTING_MINIFY_JS];

            $this->settings[$setting_name] = new ZoneSettingMinify($css, $html, $js, $setting_name, $editable, $modified_time);
            break;

          case self::SETTING_MOBILE_REDIRECT:
            $isMobileRedirectEnabled = (bool) $value[self::SETTING_MOBILE_REDIRECT_ENABLED];
            $mobileSubdomain = $value[self::SETTING_MOBILE_REDIRECT_MOBILE_SUBDOMAIN];
            $isStripUriEnabled = (bool) $value[self::SETTING_MOBILE_REDIRECT_STRIP_URI];

            $this->settings[$setting_name] = new ZoneSettingMobileRedirect($isMobileRedirectEnabled, $mobileSubdomain, $isStripUriEnabled,  $setting_name, $editable, $modified_time);
            break;

          case self::SETTING_SECURITY_HEADER:
            $this->settings[$setting_name] = new ZoneSettingSecurityHeader($value, $setting_name, $editable, $modified_time);
            break;

          case self::SETTING_SECURITY_LEVEL:
            $this->settings[$setting_name] = new ZoneSettingSecurityLevel($value, $setting_name, $editable, $modified_time);
            break;

          case self::SETTING_SSL:
            $this->settings[$setting_name] = new ZoneSettingSsl($value, $setting_name, $editable, $modified_time);
            break;
        }
      }

    }
  }

  /**
   * Gets a specific setting by its name/ID.
   *
   * @param string $id
   *   The name/ID of the setting to retrieve.
   *
   * @return ZoneSettingBase
   *   The setting object given the ID passed in.
   */
  public function getSettingById($id) {
    return $this->settings[$id];
  }

  /**
   * Gets a listing of all the settings in the zone.
   *
   * @return array
   *   An array of ZoneSetting objects.
   */
  public function getSettings() {
    return $this->settings;
  }

  /**
   * Change an optional zone setting.
   *
   * @param string $id
   *   Setting Id.
   * @param ZoneSettingBase $value
   *   The new zone setting.
   */
  public function setSettingById($id, ZoneSettingBase $value) {
    $this->settings[$id] = $value;
  }

  /**
   * Gets the accumulated set of changes made to the zone via the SDK.
   *
   * @return array
   *   List of changes made to the zone.
   */
  public function getChanges() {
    $changed_settings = [];

    foreach ($this->settings as $setting_name => $setting) {
      /* @var $setting ZoneSettingBase */

      if ($setting->isModifiedLocally()) {
        $changed_settings[$setting_name] = $setting;
      }
    }
    return $changed_settings;
  }

  /**
   * Gets the current zone's zoneId.
   *
   * @return string
   *   Zone Id.
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Constructor for ZoneSettings.
   */
  public function getChangedResults() {
    $changes = $this->getChanges();
    $items = [];

    foreach ($changes as $setting) {
      /* @var ZoneSettingBase $setting */
      $setting_name = $setting->getZoneSettingName();

      // Parse the boolean values into ZoneSettingBools.
      if (in_array($setting_name, self::BOOLEAN_SETTINGS)) {
        /* @var ZoneSettingBool $setting */
        $bool_val = $setting->getValue() ? 'on' : 'off';
        $items[] = ['zoneId' => $setting_name, 'value' => $bool_val];
      }

      // Parse the integer values in to ZoneSettingInts.
      elseif (in_array($setting_name, self::INTEGER_SETTINGS)) {
        /* @var ZoneSettingInt $setting */
        $items[] = ['zoneId' => $setting_name, 'value' => $setting->getValue()];
      }

      // The remaining types are specific one offs that have additional logic
      // which cannot be accommodated by ZoneSettingBool or ZoneSettingInt.
      else {
        switch ($setting_name) {
          case self::SETTING_MINIFY:
            /* @var ZoneSettingMinify $setting */
            $minify_setting[] = [
              self::SETTING_MINIFY_CSS => $setting->isCssMinifyEnabled(),
              self::SETTING_MINIFY_JS  => $setting->isJsMinifyEnabled(),
              self::SETTING_MINIFY_HTML => $setting->isHtmlMinifyEnabled()
            ];
            $items[] = ['zoneId' => $setting_name, 'value' => $minify_setting];
            break;

          case self::SETTING_MOBILE_REDIRECT:
            /* @var ZoneSettingMobileRedirect $setting */
            $mobile_redirect_settings = [
              self::SETTING_MOBILE_REDIRECT_ENABLED =>$setting->isIsMobileRedirectEnabled(),
              self::SETTING_MOBILE_REDIRECT_MOBILE_SUBDOMAIN => $setting->getMobileSubdomain(),
              self::SETTING_MOBILE_REDIRECT_STRIP_URI => $setting->isIsStripUriEnabled()];
            $items[] = ['zoneId' => $setting_name, 'value' => $mobile_redirect_settings];
            break;

          case self::SETTING_SECURITY_HEADER:
            /* @var ZoneSettingSecurityHeader $setting */
            $items[] = ['zoneId' => $setting_name, 'value' => $setting->getValue()];
            break;

          case self::SETTING_SECURITY_LEVEL:
            /* @var ZoneSettingSecurityLevel $setting */
            $items[] = ['zoneId' => $setting_name, 'value' => $setting->getValue()];
            break;

          case self::SETTING_SSL:
            /* @var ZoneSettingSsl $setting */
            $items[] = ['zoneId' => $setting_name, 'value' => $setting->getValue()];
            break;
        }
      }

    }
    return ['items' => $items];
  }

}