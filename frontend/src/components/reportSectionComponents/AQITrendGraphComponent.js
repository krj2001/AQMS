import { Grid } from '@mui/material'
import React from 'react'
import { Bar } from 'react-chartjs-2'

const AQITrendGraph = ({sensorName, values, labels, color, status}) => {
    // const labels = ['11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00', '24:00', '01:00', '02:00', '03:00', '04:00', '05:00', '06:00', '07:00', '08:00', '09:00', '10:00' ];
    const options = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false,
                position: 'top',
            },
            title: {
            display: true,
            text: sensorName,
            },
            tooltip: {
                callbacks: {
                  title: function (dataSetValues) {
                    console.log(dataSetValues);
                    var x = dataSetValues[0]?.parsed?.x;
                    return 'Status :' + dataSetValues[0]?.dataset?.status[x];
                    }
                }
            },
        },
    };

    const data = {
        labels,
        datasets: [
            {
            label: 'AQI',
            data: values,
            // backgroundColor: 'rgba(255, 99, 132, 0.5)',
            backgroundColor: color,
            status: status
            },
        ],
    };

  return (
    <Grid>
        <Bar options={options} data={data} width={100} height={200} />
    </Grid>
  )
}

export default AQITrendGraph