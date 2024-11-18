@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-5">
        <!-- Card -->
        <div class="bg-gray-800 rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-cyan-500">Statics</h2>
            <p class="my-4 text-gray-300">
                This is your main dashboard where you can access all your projects, manage settings, and view detailed
                analytics. Explore the features and take control of your tasks with ease!
                analytics. Explore the features and take control of your tasks with ease!
                analytics. Explore the features and take control of your tasks with ease!

            </p>
            <div id="chart"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        let options = {
            series: [{
                    name: "High - 2013",
                    data: [28, 29, 33, 36, 32, 32, 33]
                },
                {
                    name: "Low - 2013",
                    data: [12, 11, 14, 18, 17, 13, 13]
                }
            ],
            chart: {
                height: 350,
                type: 'line',
                dropShadow: {
                    enabled: true,
                    color: '#06b6d4',
                    top: 18,
                    left: 7,
                    blur: 10,
                    opacity: 0.2
                },
                zoom: {
                    enabled: false
                },
                toolbar: {
                    show: false
                }
            },
            colors: ['#06b6d4', '#d1d5db'],
            dataLabels: {
                enabled: true,
            },
            stroke: {
                curve: 'smooth'
            },
            title: {
                text: 'Average High & Low Temperature',
                align: 'left',
                style: {
                    color: '#d1d5db'
                }
            },
            grid: {
                borderColor: '#06b6d4',
                row: {
                    colors: ['#111827', 'transparent'],
                    opacity: 0.5
                },
            },
            markers: {
                size: 1
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                labels: {
                    style: {
                        colors: '#d1d5db'
                    }
                },
                title: {
                    text: 'Month',
                    style: {
                        color: '#d1d5db'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#d1d5db'
                    }
                },
                title: {
                    text: 'Temperature',
                    style: {
                        color: '#d1d5db'
                    }
                },
                min: 5,
                max: 40
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                floating: true,
                offsetY: -25,
                offsetX: -5,
                labels: {
                    colors: '#d1d5db' // Legend yazı rengi beyaz
                }
            }
        };

        let chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();
    </script>
@endsection
