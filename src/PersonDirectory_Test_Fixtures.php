<?php


if ( defined( 'WP_CLI' ) && WP_CLI ) {

	class PersonDirectory_Test_Fixtures {

		/**
		 * Seeds test 'person' posts with featured images for Playwright.
		 *
		 * @subcommand seed-people
		 *
		 * ## EXAMPLES
		 *     wp nrfc-person-directory seed-people
		 */
		public function seed_people( $args, $assoc_args ) {
			$fixtures = [
				[
					'name'  => 'Jane Doe',
					'phone' => '+44 1603 555019',
					'email' => 'jane.doe@example.com',
					'image' => __DIR__ . '/../e2e/fixtures/person1.jpeg', // Local fixture image
				],
				[
					'name'  => 'John Doe',
					'phone' => '+44 1603 555020',
					'email' => 'john.Doe@example.com',
					'image' => __DIR__ . '/../e2e/fixtures/person2.jpeg',
				],
				[
					'name'  => 'John Smith',
					'phone' => '+44 1603 555020',
					'email' => 'john.smith@example.com',
					'image' => __DIR__ . '/../e2e/fixtures/person3.jpeg',
				],
			];

			$created_ids = [];

			foreach ( $fixtures as $person ) {
				$post_id = $this->create_fixture_person(
					$person['name'],
					$person['phone'],
					$person['email'],
					$person['image']
				);

				if ( ! is_wp_error( $post_id ) ) {
					$created_ids[] = $post_id;
				}
			}

			// Output JSON so Playwright can parse created IDs if needed
			WP_CLI::line( wp_json_encode( [ 'seeded_ids' => $created_ids ] ) );
		}

		/**
		 * Deletes all 'person' posts flagged as Playwright fixtures along with their attachments.
		 *
		 * @subcommand cleanup-people
		 *
		 * ## EXAMPLES
		 *     wp nrfc-person-directory cleanup-people
		 */
		public function cleanup_people( $args, $assoc_args ) {
			$posts = get_posts( [
				'post_type'      => 'person',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'meta_key'       => '_is_playwright_fixture',
				'meta_value'     => '1',
				'fields'         => 'ids',
			] );

			$deleted_count = 0;

			foreach ( $posts as $post_id ) {
				// 1. Delete the attached featured image file and attachment record
				$thumbnail_id = get_post_thumbnail_id( $post_id );
				if ( $thumbnail_id ) {
					wp_delete_attachment( $thumbnail_id, true ); // true = force delete, bypass trash
				}

				// 2. Force delete the person post and all its postmeta
				wp_delete_post( $post_id, true );
				$deleted_count++;
			}

			WP_CLI::success( "Cleaned up {$deleted_count} Playwright person fixtures." );
		}

		/**
		 * Helper to create a single fixture person from a local image asset.
		 */
		private function create_fixture_person( string $name, string $phone, string $email, string $local_image_path ) {
			$post_id = wp_insert_post( [
				'post_title'  => sanitize_text_field( $name ),
				'post_type'   => 'person',
				'post_status' => 'publish',
				'meta_input'  => [
					'_person_phone'          => sanitize_text_field( $phone ),
					'_person_email'          => sanitize_email( $email ),
					'_is_playwright_fixture' => '1',
				],
			], true );

			if ( is_wp_error( $post_id ) ) {
				return $post_id;
			}

			if ( file_exists( $local_image_path ) ) {
				require_once ABSPATH . 'wp-admin/includes/media.php';
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/image.php';

				// Copy local fixture to temp file so media_handle_sideload doesn't delete your repo's fixture image
				$tmp_file = wp_tempnam( basename( $local_image_path ) );
				copy( $local_image_path, $tmp_file );

				$file_array = [
					'name'     => basename( $local_image_path ),
					'tmp_name' => $tmp_file,
				];

				$attachment_id = media_handle_sideload( $file_array, $post_id, $name );

				if ( ! is_wp_error( $attachment_id ) ) {
					set_post_thumbnail( $post_id, $attachment_id );
				} else {
					@unlink( $tmp_file );
				}
			}

			return $post_id;
		}
	}

	WP_CLI::add_command( 'nrfc-person-directory', 'PersonDirectory_Test_Fixtures' );
}