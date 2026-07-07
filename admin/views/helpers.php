<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function cten_field_id( string $key, string $prefix = '' ): string {
	$base = 'cten_' . str_replace( array( '[', ']', ' ' ), array( '_', '', '_' ), $key );
	$prefix = '' !== $prefix ? sanitize_key( $prefix ) . '_' : '';
	return $prefix . $base;
}

function cten_render_text( string $label, string $name, string $value, string $type = 'text', string $help = '', string $prefix = '' ): void {
	$id = cten_field_id( $name, $prefix );
	?>
	<p class="cten-field">
		<label for="<?php echo esc_attr( $id ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label>
		<input id="<?php echo esc_attr( $id ); ?>" class="regular-text" type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
		<?php if ( $help ) : ?><span class="description"><?php echo esc_html( $help ); ?></span><?php endif; ?>
	</p>
	<?php
}

function cten_render_textarea( string $label, string $name, string $value, string $help = '', string $prefix = '' ): void {
	$id = cten_field_id( $name, $prefix );
	?>
	<p class="cten-field">
		<label for="<?php echo esc_attr( $id ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label>
		<textarea id="<?php echo esc_attr( $id ); ?>" class="large-text" rows="4" name="<?php echo esc_attr( $name ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
		<?php if ( $help ) : ?><span class="description"><?php echo esc_html( $help ); ?></span><?php endif; ?>
	</p>
	<?php
}

function cten_render_checkbox( string $label, string $name, bool $value, string $help = '', string $prefix = '' ): void {
	$id = cten_field_id( $name, $prefix );
	?>
	<p class="cten-field cten-field--checkbox">
		<label for="<?php echo esc_attr( $id ); ?>">
			<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="0">
			<input id="<?php echo esc_attr( $id ); ?>" type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $value ); ?>>
			<strong><?php echo esc_html( $label ); ?></strong>
		</label>
		<?php if ( $help ) : ?><span class="description"><?php echo esc_html( $help ); ?></span><?php endif; ?>
	</p>
	<?php
}

function cten_render_select( string $label, string $name, array $options, string $selected, string $help = '', string $prefix = '' ): void {
	$id = cten_field_id( $name, $prefix );
	?>
	<p class="cten-field">
		<label for="<?php echo esc_attr( $id ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label>
		<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>">
			<?php foreach ( $options as $value => $text ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $selected, $value ); ?>><?php echo esc_html( $text ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php if ( $help ) : ?><span class="description"><?php echo esc_html( $help ); ?></span><?php endif; ?>
	</p>
	<?php
}

function cten_render_number( string $label, string $name, $value, string $help = '', int $min = 0, int $max = 9999, string $prefix = '' ): void {
	$id = cten_field_id( $name, $prefix );
	?>
	<p class="cten-field">
		<label for="<?php echo esc_attr( $id ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label>
		<input id="<?php echo esc_attr( $id ); ?>" type="number" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" min="<?php echo esc_attr( (string) $min ); ?>" max="<?php echo esc_attr( (string) $max ); ?>">
		<?php if ( $help ) : ?><span class="description"><?php echo esc_html( $help ); ?></span><?php endif; ?>
	</p>
	<?php
}
