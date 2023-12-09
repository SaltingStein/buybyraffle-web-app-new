<?php 	
	 add_action( 'wp_enqueue_scripts', 'buybyraffle_responsive_child_enqueue_styles' );
	 function buybyraffle_responsive_child_enqueue_styles() {
		 $parenthandle = 'parent-style'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.
		 $theme        = wp_get_theme();
		 wp_enqueue_style( $parenthandle,
			 get_template_directory_uri() . '/style.css',
			 array(),  // If the parent theme code has a dependency, copy it to here.
			 $theme->parent()->get( 'Version' )
		 );
		 wp_enqueue_style( 'child-style',
			 get_stylesheet_uri(),
			 array( $parenthandle ),
			 $theme->get( 'Version' ) // This only works if you have Version defined in the style header.
		 );
	 }


		   function normalizeNigerianPhoneNumber($phoneNumber) {
			// Define allowed Nigerian phone number prefixes
			$allowedPrefixes = [
				'0703', '0706', '0803', '0806', '0810', '0813', '0814', '0816', '0903', '0906', '0913', '0916', // MTN
				'07025', '07026', '0704', // MTN (Visafone)
				'0809', '0817', '0818', '0909', '0908', // 9Mobile
				'0701', '0708', '0802', '0808', '0812', '0901', '0902', '0904', '0907', '0912', '0911', // Airtel
				'0705', '0805', '0807', '0811', '0815', '0905', '0915', // Globacom
				'07027', '0709', // Multi-Links
				'0804', // Ntel
				'07020', // Smile
				'07028', '07029', '0819', // Starcomms
				'0707', // ZoomMobile
			];
		
			// Remove any non-digit character
			$phoneNumber = preg_replace('/\D/', '', $phoneNumber);
		
			// Check for numbers with international prefix +234 or 234
			if (strpos($phoneNumber, '234') === 0) {
				$phoneNumber = '0' . substr($phoneNumber, 3);
			}
		
			// Check if the phone number is valid (11 digits long)
			if (strlen($phoneNumber) !== 11) {
				return 'Invalid number'; // Or handle the error as needed
			}
		
			// Check if the prefix is allowed
			$prefix = substr($phoneNumber, 0, 4);
			if (!in_array($prefix, $allowedPrefixes)) {
				return false; // Or handle the error as needed
			}
		
			return $phoneNumber;
		}
		
		/**
		 * Define constants for different environments
		 **/
		
		// Define constants for different environments
		define('LOCAL_CONFIG_FILE_PATH', 'C:\wamp64\www\wordpress\buybyraffle-dcc92f760bee.json');
		define('DEVELOPMENT_CONFIG_FILE_PATH', 'C:\xampp\htdocs\buybyraffle\buybyraffle-dcc92f760bee.json');
		define('STAGING_CONFIG_FILE_PATH', '/home/master/applications/ksrazrveyz/private_html/buybyraffle-dcc92f760bee.json');
		define('PRODUCTION_CONFIG_FILE_PATH', '/home/master/applications/vbfntjqady/private_html/buybyraffle-dcc92f760bee.json');
		define('SERVER_IP', '138.68.91.147');
		define('STAGING_CONFIG_FILE_PATH_FOR_CASHTOKEN', '/home/master/applications/ksrazrveyz/private_html/cashtoken_idp_staging_env.json');
		define('PRODUCTION_CONFIG_FILE_PATH_FOR_CASHTOKEN', '/home/master/applications/vbfntjqady/private_html/cashtoken_idp_staging_env.json');
		define('expected_issuer','https://accounts.google.com');
		define('expected_audience', 'https://buybyraffle.com/wp-json/buybyraffle/v1/sendvouchersbymail');
		define('expected_email','buybyraffle-db@buybyraffle.iam.gserviceaccount.com');
		// Determine the environment (you can set this variable based on your logic)
		/*
		$expected_issuer = expected_issuer;
		$expected_audience = expected_audience;
		$expected_email = expected_email;
		switch ($environment) {
			case 'local':
				$configFilePath = LOCAL_CONFIG_FILE_PATH;
				break;
			case 'development':
				$configFilePath = DEVELOPMENT_CONFIG_FILE_PATH;
				break;
			case 'staging':
				$configFilePath = STAGING_CONFIG_FILE_PATH;
				break;
			case 'production':
				$configFilePath = PRODUCTION_CONFIG_FILE_PATH;
				break;
			default:
				$configFilePath = PRODUCTION_CONFIG_FILE_PATH; // Default to production
				
		}
		*/
		
			
		