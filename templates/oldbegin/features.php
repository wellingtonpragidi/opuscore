<?php 
if( defined('IS_WEB') && IS_WEB ) {


    append_resource( 'comment_area' );


    function template_scripts(): void {
        append_script( 'assets/js/dist.js' );
    }


    function posts_recents( array $args = [] ): void {
        $article = Container::call('Article');

        $has_image = (bool) ($args['image'] ?? false);
        $scope     = (string) ($args['size'] ?? 'thumb');

        foreach( $article->recents(6) as $show ) {
            $img = '';
            if( $has_image ) {
                $dim = Image::dimension_attrs($show->attachment->$scope ?? null);
                $alt = escattr($show->title);
                $filepath = $show->attachment->$scope->path ?? '';
                $src = upload_url($filepath);

                if( $filepath !== '' ) {
                    $img = "<span class=\"image\">
                        <img src=\"{$src}\" alt=\"{$alt}\" {$dim} />
                    </span>";
                }
            }

            $date = chronos_format( $show->created, 'd M' );

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
