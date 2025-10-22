<?php
/**
 * The template for displaying search forms in gwt_wp
 *
 * @package GWT
 * @since Government Website Template 2.0
 */
?>
<form role="search" method="get" class="search-form flex items-center bg-white rounded-full overflow-hidden w-full active:border-green-600 focus:border-green-600 shadow-lg hover:scale-x-110 duration-200 ease-in-out transition-all " action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <input type="search" class="search-field w-full pl-5 truncate m-0"
        placeholder="<?php echo esc_attr_x( 'Search &hellip;', 'placeholder', 'gwt_wp' ); ?>"
        value="<?php echo esc_attr( get_search_query() ); ?>" name="s"
        title="<?php _ex( 'Search for:', 'label', 'gwt_wp' ); ?>">
    <div class="text-gray-400 bg-transparent flex items-center justify-center px-3 bg-yellow-400 h-10">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="mx-auto" viewBox="0 0 16 16">
            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
        </svg>
    </div>
</form>