<head>
    <!-- Plotly.js -->
    <script src="js/plotly-latest.min.js"></script>
    <!-- Numeric JS -->
    <script src="js/numeric.min.js"></script>
</head>

<body>

<div id="myDiv" style="width: 480px; height: 380px;"><!-- Plotly chart will be drawn inside this DIV --></div>
<script>
    var data = [{
        values: [19, 26, 55],
        labels: ['Residential', 'Non-Residential', 'Utility'],
        type: 'pie'
    }];

    var layout = {
        height: 380,
        width: 480
    };

    Plotly.newPlot('myDiv', data, layout);
</script>
</body>