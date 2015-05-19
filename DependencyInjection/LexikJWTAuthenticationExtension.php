<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class LexikJWTAuthenticationExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if ($config['encoder_service'] === 'lexik_jwt_authentication.jwt_encoder') {
            $this->checkOpenSSLConfig($config['private_key_path'], $config['public_key_path'], $config['pass_phrase']);
        }

        $container->setParameter('lexik_jwt_authentication.private_key_path', $config['private_key_path']);
        $container->setParameter('lexik_jwt_authentication.public_key_path', $config['public_key_path']);
        $container->setParameter('lexik_jwt_authentication.pass_phrase', $config['pass_phrase']);
        $container->setParameter('lexik_jwt_authentication.token_ttl', $config['token_ttl']);
        $container->setParameter('lexik_jwt_authentication.user_identity_field', $config['user_identity_field']);
        $container->setParameter('lexik_jwt_authentication.login_path', $config['login_path']);

        $container->setAlias('lexik_jwt_authentication.encoder', $config['encoder_service']);
    }

    /**
     * Checks that configured keys exists and private key can be parsed using the passphrase
     *
     * @param string $privateKey
     * @param string $publicKey
     * @param string $passphrase
     *
     * @throws \RuntimeException
     */
    public function checkOpenSSLConfig($privateKey, $publicKey, $passphrase)
    {
        if (!openssl_pkey_get_public($publicKey)) {
            throw new \RuntimeException(sprintf(
                'Failed to read public key "%s".',
                $publicKey
            ));
        }

        if (!openssl_pkey_get_private($privateKey, $passphrase)) {
            throw new \RuntimeException(sprintf(
                'Failed to open private key "%s". Did you correctly configure the corresponding passphrase?',
                $privateKey
            ));
        }
    }
}
