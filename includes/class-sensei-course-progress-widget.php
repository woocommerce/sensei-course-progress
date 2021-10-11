<?php
/**
 * Sensei LMS Course Progress Widget
 *
 * @author 		Automattic
 * @category 	Widgets
 * @package 	Sensei/Widgets
 * @version 	1.0.0
 * @extends 	WC_Widget
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Sensei_Course_Progress_Widget extends WP_Widget {
	protected $woo_widget_cssclass;
	protected $woo_widget_description;
	protected $woo_widget_idbase;
	protected $woo_widget_title;

	/**
	 * Constructor function.
	 * @since  1.1.0
	 * @return  void
	 */
	public function __construct() {
		/* Widget variable settings. */
		$this->woo_widget_cssclass = 'widget_sensei_course_progress';
		$this->woo_widget_description = esc_html__( 'Displays the current learners progress within the current course/module (only displays on single lesson page).', 'sensei-course-progress' );
		$this->woo_widget_idbase = 'sensei_course_progress';
		$this->woo_widget_title = esc_html__( 'Sensei LMS - Course Progress', 'sensei-course-progress' );
		/* Widget settings. */
		$widget_ops = array( 'classname' => $this->woo_widget_cssclass, 'description' => $this->woo_widget_description );

		/* Widget control settings. */
		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => $this->woo_widget_idbase );

		/* Create the widget. */
		parent::__construct( $this->woo_widget_idbase, $this->woo_widget_title, $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {

		global $woothemes_sensei, $post, $current_user, $view_lesson, $user_taking_course;

        $allmodules = 'off';
		if ( isset( $instance['allmodules'] ) ) {
			$allmodules = $instance['allmodules'];
		}

		// If not viewing a lesson/quiz, don't display the widget
		if( ! ( is_singular( 'lesson' ) || is_singular( 'quiz' ) || is_tax( 'module' ) ) ) return;

		extract( $args );

		if ( is_singular('quiz') ) {
			$current_lesson_id = absint( get_post_meta( $post->ID, '_quiz_lesson', true ) );
		} else {
			$current_lesson_id = $post->ID;
		}

		// get the course for the current lesson/quiz
		$lesson_course_id = absint( get_post_meta( $current_lesson_id, '_lesson_course', true ) );

		//Check for preview lesson
		$is_preview = false;
		if ( method_exists( 'WooThemes_Sensei_Utils', 'is_preview_lesson' ) ) {
			$is_preview = WooThemes_Sensei_Utils::is_preview_lesson( $post->ID );
		}

		$course_title = get_the_title( $lesson_course_id );
		$course_url = get_the_permalink( $lesson_course_id );

		$in_module = false;
		$lesson_module = '';
		$lesson_array = array();

		if ( 0 < $current_lesson_id ) {
			// get an array of lessons in the module if there is one
			if( isset( Sensei()->modules ) ) {
				// Get all modules
    			$course_modules = Sensei()->modules->get_course_modules( $lesson_course_id );
				$lesson_module = Sensei()->modules->get_lesson_module( $current_lesson_id );
				$in_module = true;

				// Get an array of module ids.
				$course_module_ids = array();
				foreach ( $course_modules as $module ) {
					$course_module_ids[] = $module->term_id;
				}

				// Display all modules
				if ( 'on' === $allmodules ) {
					foreach ($course_modules as $module) {
						// get all lessons in the module
						$args = array(
							'post_type' => 'lesson',
							'post_status' => 'publish',
							'posts_per_page' => -1,
							'meta_query' => array(
								array(
									'key' => '_lesson_course',
									'value' => absint( $lesson_course_id ),
									'compare' => '='
								)
							),
							'tax_query' => array(
								array(
									'taxonomy' => Sensei()->modules->taxonomy,
									'field' => 'id',
									'terms' => absint( $module->term_id )
								)
							),
							'meta_key' => '_order_module_' . intval( $module->term_id ),
							'orderby' => 'meta_value_num date',
							'order' => 'ASC'
						);
						$lesson_array = array_merge( $lesson_array, get_posts( $args) );
					}

					// Get all lessons in the course that are not in any of the
					// course's modules.
					$args = array(
						'post_type' => 'lesson',
						'post_status' => 'publish',
						'posts_per_page' => -1,
						'meta_query' => array(
							array(
								'key' => '_lesson_course',
								'value' => absint( $lesson_course_id ),
								'compare' => '='
							)
						),
						'tax_query' => array(
							array(
								'taxonomy' => Sensei()->modules->taxonomy,
								'field'    => 'id',
								'terms'    => $course_module_ids,
								'operator' => 'NOT IN',
							)
						),
						'meta_key' => '_order_' . intval( $lesson_course_id ),
						'orderby' => 'meta_value_num date',
						'order' => 'ASC'
					);
					$lesson_array = array_merge( $lesson_array, get_posts( $args) );
				} else {
					// Only display current module
			    	// get all lessons in the current module
					$args = array(
						'post_type' => 'lesson',
						'post_status' => 'publish',
						'posts_per_page' => -1,
						'meta_query' => array(
							array(
								'key' => '_lesson_course',
								'value' => absint( $lesson_course_id ),
								'compare' => '='
							)
						),
					);

					if ( ! empty( $lesson_module ) && in_array( $lesson_module->term_id, $course_module_ids ) ) {
						$args['tax_query'] = array(
							array(
								'taxonomy' => Sensei()->modules->taxonomy,
								'field'    => 'id',
								'terms'    => intval( $lesson_module->term_id ),
							),
						);
						$args['meta_key']  = '_order_module_' . absint( $lesson_module->term_id );
						$args['orderby']   = 'meta_value_num date';
						$args['order']     = 'ASC';
					} else {
						$args['tax_query'] = array(
							array(
								'taxonomy' => Sensei()->modules->taxonomy,
								'field'    => 'id',
								'terms'    => $course_module_ids,
								'operator' => 'NOT IN',
							),
						);
						$args['meta_key']  = '_order_' . absint( $lesson_course_id );
						$args['orderby']   = 'meta_value_num date';
						$args['order']     = 'ASC';
					}

					$lesson_array = get_posts( $args );
				}
			} else {
				// if modules are not loaded, get all lessons in the course.
				$lesson_array = Sensei()->course->course_lessons( $lesson_course_id );
			}
		}

		echo wp_kses_post( $before_widget );
    ?>

		<header>
			<h2 class="course-title"><a href="<?php echo esc_url( $course_url ); ?>"><?php echo esc_html( $course_title ); ?></a></h2>
		</header>

		<?php
		$nav_array = sensei_get_prev_next_lessons( $current_lesson_id );
		if ( isset( $nav_array['previous'] ) || isset( $nav_array['next'] ) ) { ?>

			<ul class="course-progress-navigation">
				<?php if ( isset( $nav_array['previous'] ) ) { ?><li class="prev"><a href="<?php echo esc_url( $nav_array['previous']['url'] ); ?>" title="<?php echo esc_attr( $nav_array['previous']['name'] ); ?>"><span><?php esc_html_e( 'Previous', 'sensei-course-progress' ); ?></span></a></li><?php } ?>
				<?php if ( isset( $nav_array['next'] ) ) { ?><li class="next"><a href="<?php echo esc_url( $nav_array['next']['url'] ); ?>" title="<?php echo esc_attr( $nav_array['next']['name'] ); ?>"><span><?php esc_html_e( 'Next', 'sensei-course-progress' ); ?></span></a></li><?php } ?>
			</ul>

		<?php } ?>

	<details class="course-progress-details" open>
		<summary class="course-progress-summary">
			<div class="course-progress-collapse">
				<?php echo esc_html__( 'Collapse', 'sensei-course-progress' ); ?>
			</div>
			<div class="course-progress-expand">
				<?php echo esc_html__( 'Expand', 'sensei-course-progress' ); ?>
			</div>
		</summary>
		<ul class="course-progress-lessons">

			<?php

			$old_module = false;

			foreach( $lesson_array as $lesson ) {
				$lesson_id = absint( $lesson->ID );
				$lesson_title = htmlspecialchars( $lesson->post_title );
				$lesson_url = get_the_permalink( $lesson_id );

				// add 'completed' class to completed lessons
				$classes = "not-completed";
				if( WooThemes_Sensei_Utils::user_completed_lesson( $lesson->ID, $current_user->ID ) ) {
					$classes = "completed";
				}

				// Lesson Quiz Meta
                $lesson_quiz_id = absint( Sensei()->lesson->lesson_quizzes( $lesson_id ) );

				// add 'current' class on the current lesson/quiz
				if( ! is_tax( 'module' ) && ( $lesson_id === $post->ID || $lesson_quiz_id === $post->ID ) ) {
					$classes .= " current";
				}

				if ( isset( Sensei()->modules ) ) {
					$new_module = Sensei()->modules->get_lesson_module( $lesson_id );

					// Note that if there are no modules, all the modules for
					// the lessons will == false and so no module header will
					// be displayed here.
					if ( $old_module != $new_module ) {
						if ( $new_module ) {
							$module_title = $this->get_module_title_content( $new_module );
						} else {
							$module_title = esc_html( __( 'Other Lessons', 'sensei-course-progress' ) );
						}

						?>
						<li class="course-progress-module">
							<h3 class="module-title">
								<?php echo wp_kses_post( $module_title ); ?>
							</h3>
						</li>
						<?php
						$old_module = $new_module;
					}
				}

				?>

				<li class="course-progress-lesson <?php echo esc_attr( $classes ); ?>">
					<?php if( ! is_tax( 'module' ) && ( $lesson->ID === $post->ID || $lesson_quiz_id === $post->ID ) ) {
						echo '<span>' . esc_html( $lesson_title ) . '</span>';
					} else {
						echo '<a href="' . esc_url( $lesson_url ) . '">' . esc_html( $lesson_title ) . '</a>';
					} ?>
				</li>

			<?php } ?>

		</ul>
	</details>

		<?php echo wp_kses_post( $after_widget );
	}

	/**
	 * Method to update the settings from the form() method.
	 * @since  1.0.0
	 * @param  array $new_instance New settings.
	 * @param  array $old_instance Previous settings.
	 * @return array               Updated settings.
	 */
	public function update ( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* The check box is returning a boolean value. */
		$instance['allmodules'] = isset( $new_instance['allmodules'] ) ? esc_html( $new_instance['allmodules'] ) : '';

		return $instance;
	} // End update()

	/**
	 * The form on the widget control in the widget administration area.
	 * Make use of the get_field_id() and get_field_name() function when creating your form elements. This handles the confusing stuff.
	 * @since  1.1.0
	 * @param  array $instance The settings for this instance.
	 * @return void
	 */
    public function form( $instance ) {

		/* Set up some default widget settings. */
		/* Make sure all keys are added here, even with empty string values. */
		$defaults = array(
						'allmodules' => false
					);

		$instance = wp_parse_args( (array) $instance, $defaults );

		if ( isset( Sensei()->modules ) ) {
		?>
				<p>
					<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id('allmodules') ); ?>" name="<?php echo esc_attr( $this->get_field_name('allmodules') ); ?>"<?php checked( $instance['allmodules'], 'on' ); ?> />
					<label for="<?php echo esc_attr( $this->get_field_id('allmodules') ); ?>"><?php esc_html_e( 'Display all Modules', 'sensei-course-progress' ); ?></label><br />
				</p>
		<?php } else { ?>
				<p><?php esc_html_e( 'There are no options for this widget.', 'sensei-course-progress' ); ?></p>
				<?php }
	} // End form()

	/**
	 * Formats the title for each module in the course outline.
	 *
	 * @param WP_Term $module
	 * @return string
	 */
	private function get_module_title_content( WP_Term $module ) {
		$link_to_module = false;

		if ( method_exists( Sensei()->modules, 'do_link_to_module' ) ) {
			$link_to_module = Sensei()->modules->do_link_to_module( $module );
		}

		if ( $link_to_module ) {
			return '<a href="' . esc_url( $module->url ) . '">' . esc_html( $module->name ) . '</a>';
		}

		return esc_html( $module->name );
	} // End get_module_title_content()

}
