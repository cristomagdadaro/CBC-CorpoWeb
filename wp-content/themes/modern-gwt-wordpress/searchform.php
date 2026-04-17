<?php
/**
 * The template for displaying search forms in gwt_wp
 *
 * @package GWT
 * @since Government Website Template 2.0
 */
?>
<form role="search" method="get" class="search-form flex border-2 border-gray-300 hover:border-gray-400 focus-within:border-green-500 focus-within:ring-2 focus-within:ring-green-100 items-center bg-white rounded-full overflow-hidden w-full transition-all duration-200 h-11 shadow-sm" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <label for="search-field" class="sr-only"><?php _ex( 'Search for:', 'label', 'gwt_wp' ); ?></label>
    <input
            id="search-field"
            type="search"
            class="search-field w-full pl-5 pr-2 truncate m-0 !font-spartan z-10 bg-transparent border-none focus:outline-none text-gray-700 placeholder-gray-400"
            placeholder="<?php echo esc_attr_x( 'Search &hellip;', 'placeholder', 'gwt_wp' ); ?>"
            value="<?php echo esc_attr( get_search_query() ); ?>"
            name="s"
            autocomplete="off"
            aria-label="<?php _ex( 'Search for:', 'label', 'gwt_wp' ); ?>"
    >
    <button
            type="submit"
            class="text-gray-500 hover:text-gray-700 active:text-gray-900 bg-yellow-400 hover:bg-yellow-500 active:bg-yellow-600 flex items-center justify-center px-4 h-full z-10 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-yellow-600"
            aria-label="<?php echo esc_attr_x( 'Submit search', 'button label', 'gwt_wp' ); ?>"
    >
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
        </svg>
    </button>
</form>