<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wp_sparksoft2.2' );

/** MySQL database username */
define( 'DB_USER', 'Eilon' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '}b8N{1 =7ty{YsdGnf/$}|m3 ozz9J$yBmDh{FyxRidP>*CW:{1i7G{b{}<N](Y9' );
define( 'SECURE_AUTH_KEY',  '*b4t@-%5qUk$m13kfj32]=FUuc+C^{W;j335F^*,g>A}fy=S+hsQ3;mR`Vz VSe1' );
define( 'LOGGED_IN_KEY',    'lI+aS&.;;#P@_1eR&FTbJoAVz`[t:w7iz93$Zhzkvw[7Jf.zwbC^y$VDgO)hn:VB' );
define( 'NONCE_KEY',        '!t&VE%(0W ts@+k]BS=PDK;f6K7$4F(ng3P5z^?#%$rx,Py;:PKbxo/B_vDG=%8n' );
define( 'AUTH_SALT',        '6.y~+di-/&)6-ha-m[66<Mq<g>MPJI9gM|eXVlcfGX8vsb>H*jlV>wkkZM~DmwxK' );
define( 'SECURE_AUTH_SALT', '~BWVGH;!={Fmo?6HG9+X3^_{9a-FMg%[(7OpiB2)GYYD1za**}!)t~oj1-1e=_XK' );
define( 'LOGGED_IN_SALT',   '6Fi*6#%t|w,@W@BPj?Eq%?@L7i0.I3ui~~6+vs5@3|L}%|k-wfsan`S_yowo~=[x' );
define( 'NONCE_SALT',       'tTy*|%ed1,GPWvFGY(2v*_D!Fe@{YjlLt:8;_ x<Tqxn%xnnSgoZv^/T7vC@;r?^' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', true );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
