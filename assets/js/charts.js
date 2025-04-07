// Cấu hình chung cho biểu đồ
Chart.defaults.global.defaultFontFamily = 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';

// Màu sắc cho biểu đồ
const chartColors = {
    primary: 'rgba(54, 162, 235, 0.5)',
    primaryBorder: 'rgb(54, 162, 235)',
    secondary: 'rgba(255, 206, 86, 0.5)',
    secondaryBorder: 'rgb(255, 206, 86)',
    success: 'rgba(75, 192, 192, 0.5)',
    successBorder: 'rgb(75, 192, 192)',
    danger: 'rgba(255, 99, 132, 0.5)',
    dangerBorder: 'rgb(255, 99, 132)',
    warning: 'rgba(255, 159, 64, 0.5)',
    warningBorder: 'rgb(255, 159, 64)',
    info: 'rgba(153, 102, 255, 0.5)',
    infoBorder: 'rgb(153, 102, 255)'
};

// Tạo biểu đồ cột
function createBarChart(canvasId, data, options = {}) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    return new Chart(ctx, {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            ...options
        }
    });
}

// Tạo biểu đồ đường
function createLineChart(canvasId, data, options = {}) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    return new Chart(ctx, {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            ...options
        }
    });
}

// Tạo biểu đồ tròn
function createPieChart(canvasId, data, options = {}) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    return new Chart(ctx, {
        type: 'pie',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            ...options
        }
    });
}

// Tạo biểu đồ bánh rán
function createDoughnutChart(canvasId, data, options = {}) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    return new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            ...options
        }
    });
}

// Tạo biểu đồ radar
function createRadarChart(canvasId, data, options = {}) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    return new Chart(ctx, {
        type: 'radar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            ...options
        }
    });
}

// Cập nhật dữ liệu cho biểu đồ
function updateChartData(chart, newData) {
    chart.data = newData;
    chart.update();
}

// Thêm dữ liệu mới vào biểu đồ
function addChartData(chart, label, data) {
    chart.data.labels.push(label);
    chart.data.datasets.forEach((dataset, i) => {
        dataset.data.push(data[i]);
    });
    chart.update();
}

// Xóa dữ liệu khỏi biểu đồ
function removeChartData(chart) {
    chart.data.labels.pop();
    chart.data.datasets.forEach(dataset => {
        dataset.data.pop();
    });
    chart.update();
}

// Định dạng số tiền cho trục Y
function formatMoneyAxis(value) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        maximumFractionDigits: 0
    }).format(value);
}

// Định dạng số cho trục Y
function formatNumberAxis(value) {
    return new Intl.NumberFormat('vi-VN').format(value);
}

// Định dạng phần trăm cho trục Y
function formatPercentAxis(value) {
    return value + '%';
}

// Tạo tooltip tùy chỉnh
function createCustomTooltip(tooltipItems) {
    let tooltip = '';
    tooltipItems.forEach(item => {
        const label = item.dataset.label || '';
        const value = item.raw;
        tooltip += `${label}: ${formatMoney(value)}\n`;
    });
    return tooltip;
}

// Tạo animation cho biểu đồ
function createChartAnimation(duration = 1000) {
    return {
        duration: duration,
        easing: 'easeInOutQuart',
        from: 0,
        to: 1
    };
}

// Tạo gradient cho biểu đồ
function createGradient(ctx, color1, color2) {
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, color1);
    gradient.addColorStop(1, color2);
    return gradient;
}

// Hàm định dạng số tiền
function formatMoney(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

// Hàm định dạng ngày tháng
function formatDate(date) {
    return new Date(date).toLocaleDateString('vi-VN');
}

// Hàm tạo biểu đồ doanh thu
function createRevenueChart(ctx, data) {
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Doanh thu',
                data: data.values,
                lineTension: 0.3,
                backgroundColor: "rgba(78, 115, 223, 0.05)",
                borderColor: "rgba(78, 115, 223, 1)",
                pointRadius: 3,
                pointBackgroundColor: "rgba(78, 115, 223, 1)",
                pointBorderColor: "rgba(78, 115, 223, 1)",
                pointHoverRadius: 3,
                pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                pointHitRadius: 10,
                pointBorderWidth: 2,
                fill: true
            }]
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0
                }
            },
            scales: {
                xAxes: [{
                    gridLines: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        maxTicksLimit: 7
                    }
                }],
                yAxes: [{
                    ticks: {
                        maxTicksLimit: 5,
                        padding: 10,
                        callback: function(value) {
                            return formatMoney(value);
                        }
                    },
                    gridLines: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }]
            },
            legend: {
                display: false
            },
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                titleMarginBottom: 10,
                titleFontColor: '#6e707e',
                titleFontSize: 14,
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                intersect: false,
                mode: 'index',
                caretPadding: 10,
                callbacks: {
                    label: function(tooltipItem, chart) {
                        var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                        return datasetLabel + ': ' + formatMoney(tooltipItem.yLabel);
                    }
                }
            }
        }
    });
}

// Hàm tạo biểu đồ học viên mới
function createNewStudentsChart(ctx, data) {
    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Học viên mới', 'Học viên cũ'],
            datasets: [{
                data: [data.new, data.total - data.new],
                backgroundColor: ['#4e73df', '#1cc88a'],
                hoverBackgroundColor: ['#2e59d9', '#17a673'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }]
        },
        options: {
            maintainAspectRatio: false,
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                caretPadding: 10,
            },
            legend: {
                display: true,
                position: 'bottom'
            },
            cutoutPercentage: 80,
        }
    });
} 