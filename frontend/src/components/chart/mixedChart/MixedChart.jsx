import { useState, useEffect } from 'react';
import { Line } from 'react-chartjs-2';

function MixedChart({ data }) {
  /* eslint-disable-next-line */
  const [graphData, setGraphdata] = useState({
    labels: [],
    datasets: []
  });

  useEffect(() => {
    setGraphdata(()=>{
      return {
        'labels': data?.labels || [],
        datasets : legendDataSet(data),
      }
    })
  }, [data]);

  const legendDataSet = (data) =>{
    var legendArraySet = [];
    if(data?.lastData){
      legendArraySet = [...legendArraySet,
        {
          label: 'Last',
          type: 'line',
          borderColor: 'grey',
          backgroundColor: data?.colors || [],
          data: data?.lastData || [],
          fillColor: 'blue',
          pointRadius: 3.8,
        },
      ]
    }
    if(data?.warningLevelMin){
      legendArraySet = [...legendArraySet,
      {
        label: 'Warning Min',
        type: 'line',
        backgroundColor: '#ffeb3b',
        borderColor: '#ffeb3b',
        data: data?.warningLevelMin || [],
        fill: false,
        pointRadius: 0,
      },]
    }
    if(data?.warningLevelMax){
      legendArraySet = [...legendArraySet,{
        label: 'Warning Max',
        type: 'line',
        backgroundColor: '#ffeb3b',
        borderColor: '#ffeb3b',
        data: data?.warningLevelMax || [],
        fill: false,
        pointRadius: 0,
      },]
    }
    if(data?.criticalLevelMin){
      legendArraySet = [...legendArraySet,{
        label: 'Critical Min',
        type: 'line',
        backgroundColor: '#d50000',
        borderColor: '#d50000',
        data: data?.criticalLevelMin || [],
        fill: false,
        pointRadius: 0,
      },]
    }
    if(data?.criticalLevelMax){
      legendArraySet = [...legendArraySet,
        {
          label: 'Critical Max',
          type: 'line',
          backgroundColor: '#d50000',
          borderColor: '#d50000',
          data: data?.criticalLevelMax || [],
          fill: false,
          pointRadius: 0,
        },
      ]
    }
    return legendArraySet
  }
  
  return (
    <Line
      data={graphData}
      height={80}
    />

  );
}

export default MixedChart;
