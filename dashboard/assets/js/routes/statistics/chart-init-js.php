<?php 
$stats = Statistic::instance(); ?>
<script>
let Chart_Stats = new Chart(document.getElementById("chart-stats").getContext('2d'), {
    
    type: 'bar',
    data: {
        labels: [<?php 
            $stats->dates('-6 day');
            $stats->dates('-5 day');
            $stats->dates('-4 day');
            $stats->dates('-3 day');
            $stats->dates('-2 day');
            $stats->dates('-1 day');
            $stats->dates('today');
        ?>],
        datasets: [{
            label: 'Visualizações',
            data: [<?php
                $stats->views('-6 day');
                $stats->views('-5 day');
                $stats->views('-4 day');
                $stats->views('-3 day');
                $stats->views('-2 day'); 
                $stats->views('-1 day');
                $stats->views('today');
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