<?php
function action_footer() { ?>
<script>
    const BASE_URL = "<?= site_url() ?>";
    const DASH_URL = "<?= dash_url() ?>";
    const CURRENT_URL = "<?= URL::current() ?>";
</script>
<script src="<?= dash_url('assets/js/scripts.js?v=' . VERSION) ?>"></script>
<script src="<?= dash_url('assets/js/packit.js?v=' . VERSION) ?>"></script>

<?php if( 
    URL::param(0) === 'pages' && URL::param(1) === 'update' || 
    URL::param(0) === 'posts' && URL::param(1) === 'update' || 
    URL::param(0) === 'customize' && URL::param(1) === 'context' && URL::has('insert') || 
    URL::param(0) === 'customize' && URL::param(1) === 'context' && URL::has('update') 
) : 
if( editor_is('punk') ) {
?>
<script src="<?= dash_url('assets/js/punk.js?v='.VERSION) ?>"></script>
<script>
new punk ({
    selector: "#editor",
    width:    "100%",
    height:   "50vh",
    upload:   "<?= dash_url('controller/async/editor-upload.php') ?>",
    selected: "<?= dash_url('controller/async/editor-media-selected.php') ?>"
});
</script>
<script src="<?= dash_url('assets/js/editor-loadmore.js?v='.VERSION) ?>"></script>
<script>
new editLoading ({
    limit:       <?= per_load('popup') ?>,
    content:     "#gallery",
    urlLoadmore: "<?= dash_url('controller/async/editor-loadmore.php') ?>",
    urlSelected: "<?= dash_url('controller/async/editor-media-selected.php') ?>",
    urlDelete:   "<?= dash_url('controller/async/editor-delete-media.php') ?>"
});
</script>
<?php 
} // end 'editor - punk'
if( editor_is('tinymce') ) { ?>
<script src="https://cdn.tiny.cloud/1/zk1otbqmj6bcltbt3xhrx7vw58sjspoo4zjhyj3uc0kt72c8/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<?php
if( Hook::has_action('tinymce_init') ) {
    Hook::call_action('tinymce_init');
}
else { ?>
<script src="<?= dash_url('assets/js/tinymce.init.js?v='.VERSION) ?>"></script>
<?php }
} // end 'editor - tinymce'
if( editor_is('ace') ) { ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.3/ace.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.23.1/ext-language_tools.js"></script>
<script src="<?= dash_url('assets/js/ace.config.js?v='.VERSION) ?>"></script>
<?php }
if( editor_is('codemirror') ) { ?>
<!-- CodeMirror base -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<!-- Modos -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
<!-- Autocomplete -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/show-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/html-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/xml-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/selection/active-line.min.js"></script>
<script>
var editor = CodeMirror.fromTextArea(document.getElementById("editor"), {
    mode: "htmlmixed",
    lineNumbers: true,
    styleActiveLine: true,
    autoCloseTags: true,
    indentUnit: 4,
    extraKeys: {
        "Ctrl-Space": "autocomplete"
    },
    theme: "monokai"
});
editor.on("inputRead", function(cm, change) {
    if( change.text[0] === "<" ) {
        cm.showHint({completeSingle: false});
    }
});
</script>
<?php } // end 'editor - codemirror'
endif;

if( URL::param(0) === 'media' && URL::param(1) === '' && ! URL::has('id') ) : ?>
<script src="<?= dash_url('assets/js/media-loadmore.js?v='.VERSION) ?>"></script>
<script>
new LoadMore ({
    limit:   <?= per_load('page') ?>,
    content: "#gallery",
    url:     "<?= dash_url('controller/async/media-loadmore.php') ?>"
});
</script>
<?php endif;
if( URL::param(0) == 'customize' && URL::param(1) == 'menus' ) : ?>
<script>const CURRENT_MENU = "<?php echo $_GET['name'] ?? ( $_COOKIE['last_menu'] ?? null ); ?>";</script>
<script src="<?= dash_url('assets/js/menu.js?v='.VERSION) ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.6/Sortable.min.js"></script>
<script src="<?= dash_url('assets/js/sortable.init.js?v='.VERSION) ?>"></script>
<?php endif; ?>

<script src="<?= dash_url('assets/js/icons.js?v='.VERSION) ?>"></script>

<?php if( statistics() && URL::param(0) === '' ) : 
    $statistic  = GraphStatistic::instance(); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>
<script>
    var chartStatistic = new Chart(document.getElementById("chart-statistic").getContext('2d'), {
        type: 'bar',
        data: {
            labels: [<?php 
                $statistic->dates('-6 day');
                $statistic->dates('-5 day');
                $statistic->dates('-4 day');
                $statistic->dates('-3 day');
                $statistic->dates('-2 day');
                $statistic->dates('-1 day');
                $statistic->dates('today');
            ?>],
            datasets: [{
                label: 'Visualizações',
                data: [<?php
                    $statistic->views('-6 day');
                    $statistic->views('-5 day');
                    $statistic->views('-4 day');
                    $statistic->views('-3 day');
                    $statistic->views('-2 day'); 
                    $statistic->views('-1 day');
                    $statistic->views('today');
                ?>],
                backgroundColor: [<?php 
                    for($i = 1; $i <= 7; $i++) { 
                        echo '"rgba(54, 162, 235, 0.2)", ';
                    }
                ?>],
                borderColor: [<?php 
                    for($i = 1; $i <= 7; $i++) { 
                        echo '"#36A2FF", ';
                    }
                ?>],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });
</script>
<?php endif; 

}
