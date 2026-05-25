<?php 
if( defined('IS_WEB') && IS_WEB ) {    

    function template_scripts(): void {
        add_script( 'assets/js/dist.js' );
    }


    function posts_recents( array $args = [] ): void {
        $post = Container::call('Post');

        $image = (bool) ($args['image'] ?? false);
        $scope = (string) ($args['size'] ?? 'thumb');

        foreach( $post->recents(6) as $show ) {
            $img = '';
            if( $image ) {
                $dim = Image::dimensions_attrs($show->attachment->$scope ?? null);
                $alt = escattr($show->title);
                $filepath = $show->attachment->$scope->path ?? '';
                $src = upload_url($filepath);

                if( $filepath !== '' ) {
                    $img = "<span class=\"image\">
                        <img src=\"{$src}\" alt=\"{$alt}\" {$dim} />
                    </span>";
                }
            }

            $date = chronos_format($show->created, 'd M');

            echo "
            <div>
                {$img}
                <a href=\"{$show->URL}\">{$show->title}</a>
                <b>{$date}</b>
            </div>";
        }
    }

}


if( defined('IS_DASHBOARD') && IS_DASHBOARD ) {
}
