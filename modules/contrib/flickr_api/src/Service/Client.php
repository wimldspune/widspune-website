<?php

namespace Drupal\flickr_api\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use GuzzleHttp\Client as GuzzleClient;
use Drupal\Core\Url;

/**
 * Class Client.
 *
 * @package Drupal\flickr_api\Service
 */
class Client {

  use StringTranslationTrait;

  /**
   * Config.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cacheBackend;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Api uri.
   *
   * @var string
   */
  private $apiUri;

  /**
   * Host uri.
   *
   * @var string
   */
  private $hostUri;

  /**
   * API Key.
   *
   * @var string
   */
  private $apiKey;

  /**
   * API Secret.
   *
   * @var string
   */
  private $apiSecret;

  /**
   * Max Age.
   *
   * @var string
   */
  private $apiCacheMaximumAge;

  /**
   * Guzzle Client.
   *
   * @var \GuzzleHttp\Client
   */
  private $guzzleClient;

  /**
   * Client constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   Config.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   Cache backend.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   String translation.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(ConfigFactory $config,
                              CacheBackendInterface $cacheBackend,
                              TranslationInterface $stringTranslation,
                              MessengerInterface $messenger) {
    $this->config = $config->get('flickr_api.settings');
    $this->cacheBackend = $cacheBackend;
    $this->stringTranslation = $stringTranslation;
    $this->messenger = $messenger;

    $this->apiUri = $this->config->get('api_uri');
    $this->hostUri = $this->config->get('host_uri');
    $this->apiKey = $this->config->get('api_key');
    $this->apiSecret = $this->config->get('api_secret');
    $this->apiCacheMaximumAge = $this->config->get('api_cache_maximum_age');

    $this->guzzleClient = new GuzzleClient([
      'base_uri' => $this->apiUri,
    ]);

  }

  /**
   * Flickr request.
   *
   * @param string $method
   *   Method to call.
   * @param array $args
   *   Args to request.
   * @param bool $cacheable
   *   Is it cachable.
   *
   * @return bool|array
   *   Either an array or false.
   */
  public function request($method, array $args, $cacheable = TRUE) {
    // Build the arg_hash.
    $args = $this->buildArgs($args, $method);
    $argHash = $this->buildArgHash($args);

    // $response = &drupal_static(__FUNCTION__);
    // // Can be replaced with the `__METHOD__`.
    $cid = 'flickr_api:' . md5($argHash);

    // Check cache.
    if ($cache = $this->cacheBackend->get($cid)) {
      $response = $cache->data;

      // Return result from cache if found.
      return $response;
    }
    // No cache. Do it the hard way.
    else {
      // If we've got a secret, sign the arguments.
      if ($secret = $this->apiSecret) {
        $args['api_sig'] = md5($secret . $argHash);
      }

      // TODO Implement try catch.
      $response = $this->doRequest('', $args);
      if ($response) {
        // Cache the response if we got one.
        if ($this->apiCacheMaximumAge != 0 && $cacheable == TRUE) {
          $this->cacheBackend->set($cid, $response, time() + $this->apiCacheMaximumAge);
        }

        // Return result from source if found.
        return $response;
      }
    }

    // Tough luck, no results mate.
    return FALSE;
  }

  /**
   * Build default args.
   *
   * @param array $args
   *   Args to request.
   * @param string $method
   *   Method to call.
   * @param string $format
   *   Format to request.
   *
   * @return array
   *   Return the args array.
   */
  private function buildArgs(array $args, $method, $format = 'json') {
    // Add in additional parameters then sort them for signing.
    $args['api_key'] = $this->apiKey;
    $args['method'] = $method;
    $args['format'] = $format;
    $args['nojsoncallback'] = 1;
    ksort($args);

    return $args;
  }

  /**
   * Build Hash from Args array.
   *
   * @param array $args
   *   Args to request.
   *
   * @return string
   *   Return string.
   */
  private function buildArgHash(array $args) {
    // Build an argument hash API signing (we'll also use it for the cache id).
    $argHash = '';

    foreach ($args as $k => $v) {
      $argHash .= $k . $v;
    }

    return $argHash;
  }

  /**
   * Guzzle request for Flickr.
   *
   * @param string $url
   *   Url.
   * @param array $parameters
   *   Parameters.
   * @param string $requestMethod
   *   Request method.
   *
   * @return bool|array
   *   False or array.
   */
  private function doRequest($url, array $parameters = [], $requestMethod = 'GET') {
    if (!$this->apiKey || !$this->apiSecret) {
      $msg = $this->t('Flickr API credentials are not set. It can be set on the <a href=":config_page">configuration page</a>.',
        [':config_page' => Url::fromRoute('flickr_api.settings')]
      );

      $this->messenger->addError($msg);
      return FALSE;
    }

    $response = $this->guzzleClient->request($requestMethod, $url, ['query' => $parameters]);

    // TODO Error checking can be improved.
    if ($response->getStatusCode() == !200) {
      return FALSE;
    }

    // TODO Add some checking.
    $body = $response->getBody();

    // TODO Add some checking.
    return json_decode((string) $body, TRUE);
  }

}
