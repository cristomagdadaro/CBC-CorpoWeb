<?php

namespace CbcFormManager\Presentation\cbc_feedback_form;

use CbcFormManager\Application\FormModuleInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Form implements FormModuleInterface {
    public function key(): string {
        return 'cbc_feedback_form';
    }

    public function shortcode(): string {
        return 'cbc_feedback_form';
    }

    public function title(): string {
        return __( 'CBC Feedback Form', 'cbc-form-manager' );
    }

    public function fields(): array {
        return [
                'name'     => [ 'label'    => __( 'Your Name', 'cbc-form-manager' ),
                                'type'     => 'text',
                                'required' => true
                ],
                'email'    => [
                        'label'    => __( 'Your Email', 'cbc-form-manager' ),
                        'type'     => 'email',
                        'required' => true
                ],
                'rating'   => [
                        'label'    => __( 'Rating', 'cbc-form-manager' ),
                        'type'     => 'select',
                        'required' => true,
                        'options'  => [
                                '5' => __( 'Excellent', 'cbc-form-manager' ),
                                '4' => __( 'Very Good', 'cbc-form-manager' ),
                                '3' => __( 'Good', 'cbc-form-manager' ),
                                '2' => __( 'Fair', 'cbc-form-manager' ),
                                '1' => __( 'Poor', 'cbc-form-manager' ),
                        ]
                ],
                'feedback' => [ 'label'    => __( 'Feedback Message', 'cbc-form-manager' ),
                                'type'     => 'textarea',
                                'required' => true
                ],
        ];
    }

    public function enqueue_assets(): void {
        //wp_enqueue_style('cbc-feedback-form', CBC_FM_PLUGIN_URL . 'presentation/cbc_feedback_form/assets/style.css', [], '1.0.1');
        wp_enqueue_script( 'cbc-feedback-form', CBC_FM_PLUGIN_URL . 'presentation/cbc_feedback_form/assets/script.js', [ 'jquery' ], '1.0.0', true );
    }

    public function render( array $view = [] ): string {
        $fields       = $this->fields();
        $errors       = $view['errors'] ?? [];
        $old          = $view['old'] ?? [];
        $success      = $view['success'] ?? null;
        $action       = $view['action'] ?? '';
        $nonce_action = $view['nonce_action'] ?? '';
        $nonce_name   = $view['nonce_name'] ?? '_cbc_form_nonce';

        $id = function ( string $name ): string {
            return 'cbc_feedback_' . $name;
        };

        ob_start();
        ?>
        <form class="cbc-form cbc-feedback-form max-w-lg mx-auto border rounded-md p-8 space-y-6" method="post"
              action="<?php echo esc_url( $action ); ?>">
            <input type="hidden" name="_cbc_form_key" value="<?php echo esc_attr( $this->key() ); ?>"/>
            <?php wp_nonce_field( $nonce_action, $nonce_name ); ?>

            <?php if ( ! empty( $errors['_global'] ) ): ?>
                <div class="p-3 bg-red-100 text-red-700 rounded-lg text-sm"><?php echo esc_html( $errors['_global'] ); ?></div>
            <?php endif; ?>

            <?php if ( $success ): ?>
                <div class="p-3 bg-green-100 text-green-700 rounded-lg text-sm"><?php echo esc_html( $success ); ?></div>
            <?php endif; ?>

            <div>
                <label for="<?php echo esc_attr( $id( 'name' ) ); ?>"
                       class="block text-sm font-medium text-gray-700 mb-1">
                    <?php echo esc_html( $fields['name']['label'] ); ?> *
                </label>
                <input
                        id="<?php echo esc_attr( $id( 'name' ) ); ?>"
                        type="text"
                        name="name"
                        value="<?php echo esc_attr( $old['name'] ?? '' ); ?>"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                />
                <?php if ( ! empty( $errors['name'] ) ): ?>
                    <p class="text-red-600 text-sm mt-1"><?php echo esc_html( $errors['name'] ); ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="<?php echo esc_attr( $id( 'email' ) ); ?>"
                       class="block text-sm font-medium text-gray-700 mb-1">
                    <?php echo esc_html( $fields['email']['label'] ); ?> *
                </label>
                <input
                        id="<?php echo esc_attr( $id( 'email' ) ); ?>"
                        type="email"
                        name="email"
                        value="<?php echo esc_attr( $old['email'] ?? '' ); ?>"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                />
                <?php if ( ! empty( $errors['email'] ) ): ?>
                    <p class="text-red-600 text-sm mt-1"><?php echo esc_html( $errors['email'] ); ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="<?php echo esc_attr( $id( 'rating' ) ); ?>"
                       class="block text-sm font-medium text-gray-700 mb-1">
                    <?php echo esc_html( $fields['rating']['label'] ); ?> *
                </label>
                <select
                        id="<?php echo esc_attr( $id( 'rating' ) ); ?>"
                        name="rating"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                >
                    <option value="">—</option>
                    <?php foreach ( $fields['rating']['options'] as $val => $label ): ?>
                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( ( $old['rating'] ?? '' ), $val ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ( ! empty( $errors['rating'] ) ): ?>
                    <p class="text-red-600 text-sm mt-1"><?php echo esc_html( $errors['rating'] ); ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="<?php echo esc_attr( $id( 'feedback' ) ); ?>"
                       class="block text-sm font-medium text-gray-700 mb-1">
                    <?php echo esc_html( $fields['feedback']['label'] ); ?> *
                </label>
                <textarea
                        id="<?php echo esc_attr( $id( 'feedback' ) ); ?>"
                        name="feedback"
                        rows="5"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                ><?php echo esc_textarea( $old['feedback'] ?? '' ); ?></textarea>
                <?php if ( ! empty( $errors['feedback'] ) ): ?>
                    <p class="text-red-600 text-sm mt-1"><?php echo esc_html( $errors['feedback'] ); ?></p>
                <?php endif; ?>
            </div>

            <div class="pt-3 cbc-form-actions">
                <button type="submit" class="w-full bg-[#1f5d2b] hover:bg-[#a2b917] text-white font-semibold py-2.5 px-4 rounded-md transition duration-150 ease-in-out">
                    <?php echo esc_html__( 'Send Feedback', 'cbc-form-manager' ); ?>
                </button>
            </div>
        </form>
        <?php
        return (string) ob_get_clean();
    }
}
