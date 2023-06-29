import { Button, Dialog, DialogContent, DialogTitle, Grid, LinearProgress, Typography } from '@mui/material'
import { Stack } from '@mui/system';
import React, { useEffect, useState } from 'react'
import { Bar } from 'react-chartjs-2';
import { FetchSensorAQIService } from '../../services/LoginPageService';
import AQITrendGraph from './AQITrendGraphComponent';

const AQITrendModal = ({openTrend, setOpenTrend, id, type}) => {

    const [dataSet, setDataSet] = useState({});
    //     {
    //     labels: ['12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00', '24:00', '01:00', '02:00', '03:00', '04:00', '05:00', '06:00', '07:00', '08:00', '09:00', '10:00', '11:00' ],
    //     sensorList: [
    //         {
    //             sensorName: 'SO2',
    //             values: [75,19,6,25,37,64,65,49,64,-25,85,65,49,64,25,85,75,19,6,25,37,64,94,64],
    //             color: ['#3ff', '#15f', '#951', '#c54', '#9af', '#321', '#649', '#a29', '#650', '#176', '#649', '#286', '#459', '#110', '#340', '#709', '#601', '#011', '#940', '#027', '#780', '#860', '#431', '#118'],
    //             status: ['Good', 'Sever', 'Average', 'Moderate', 'Poor', 'Good', 'Good', 'Satisfactory', 'Good', 'Average', 'Moderate', 'Poor', 'Good', 'Good', 'Average', 'Moderate', 'Poor', 'Good', 'Good', 'Average', 'Moderate', 'Poor', 'Good','Good',]
    //         },
    //         {
    //             sensorName: 'PM2.5',
    //             values: [65,49,64,25,85,75,19,6,25,37,64,94,64,75,19,6,25,37,64,65,49,64,25,85],
    //             color: [ '#321', '#649', '#a29', '#650', '#3ff', '#15f', '#951', '#c54', '#9af', '#340', '#709',  '#780', '#860', '#431', '#118', '#176', '#649', '#601', '#011', '#940', '#027', '#286', '#459', '#110'],
    //             status: ['Poor', 'Good', 'Good', 'Satisfactory', 'Good', 'Average', 'Moderate', 'Poor', 'Good', 'Good', 'Sever', 'Good', 'Good', 'Average', 'Average', 'Moderate', 'Good', 'Average', 'Moderate', 'Poor', 'Moderate', 'Poor', 'Good','Good',]
    //         },
    //         {
    //             sensorName: 'PM10',
    //             values: [37,64,65,75,19,64,25,85,75,6,25,85,65,49,19,6,25,49,64,25,37,64,94,64],
    //             color: ['#011', '#940', '#027', '#780', '#649', '#a29', '#650', '#176', '#709', '#601', '#860', '#649', '#286', '#459', '#110', '#340', '#431', '#118', '#3ff', '#15f', '#951', '#c54', '#9af', '#321'],
    //             status: ['Good', 'Sever', 'Average', 'Moderate', 'Poor', 'Good', 'Good', 'Satisfactory', 'Good', 'Average', 'Moderate', 'Poor', 'Good', 'Good', 'Average', 'Moderate', 'Poor', 'Good', 'Good', 'Average', 'Moderate', 'Poor', 'Good','Good',]
    //         },
    //         {
    //             sensorName: 'NO2',
    //             values: [-15,25,37,65,64,94,64,75,37,19,61,25,64,-25,64,65,49,19,49,64,25,85,75,34],
    //             color: ['#118', '#176', '#649', '#601', '#011', '#3ff', '#15f', '#951', '#c54', , '#431', '#940', '#027', '#286', '#459', '#110', '#9af', '#321', '#649', '#a29', '#650', '#340', '#709',  '#780', '#860'],
    //             status: ['Good', 'Average', 'Moderate', 'Poor', 'Good', 'Good', 'Good', 'Average', 'Moderate', 'Poor', 'Moderate', 'Poor', 'Satisfactory', 'Good', 'Good', 'Average', 'Average', 'Moderate', 'Good','Good', 'Poor', 'Good', 'Good', 'Sever']
    //         },
    //     ]
    // }
    
    const [isLoading, setIsLoading] = useState(false);
    const sensorAPI = () =>{
    fetch('http://demo1993478.mockable.io/aqiTrends', {
        method : 'POST',
        mode: 'cors',
        cache: 'no-cache',
        credentials: 'same-origin',
        redirect: 'follow',
        referrerPolicy: 'no-referrer',
        body: JSON.stringify(id)

    }) .then((response) => {
    //   if (successCaseCode.indexOf(response.status) > -1) {
    //     return response.json();
    //   }
    //   // eslint-disable-next-line no-throw-literal
    //   throw {
    //     errorStatus: response.status,
    //     errorObject: response.json(),
    //   };
      return response.json();
    })
    .then((dataResponse) => {
        // successCallback(dataResponse)
        setDataSet(dataResponse);
        console.log(dataResponse);
        setTimeout(()=>{
            setIsLoading(true);
        },2000);
    })
    .catch((error) => {
      error.errorObject.then((errorResponse) => {
        console.log(error.errorStatus, errorResponse.message);
      });
    });
    }
    useEffect(()=>{
        if(openTrend === true){
            if(type === 'sensor'){
                // API call for sensor level AQI trends
                FetchSensorAQIService({device_id: id.device_id}, handleSuccess, handleException);
            } else if(type === 'device'){
                // API call for device level AQI trends
                FetchSensorAQIService({device_id: id.deviceId, fromDate: id.fromDate, toDate: id.toDate}, handleSuccess, handleException);
            }
        }
    }, [openTrend]);

    const handleSuccess = (dataObject) =>{
        setDataSet(dataObject || {});
        setTimeout(()=>{
            setIsLoading(true);
        },2000);
    }

    const handleException = () =>{ }
    
    const onClose = ()=>{
        setOpenTrend(false);
        setTimeout(()=>{
            setIsLoading(false);
            setDataSet({});
        }, 1000);
    }
  return (
    <Dialog
      fullWidth
      maxWidth="md"
      sx={{ '& .MuiDialog-paper': { width: '100%' } }}
      open={openTrend}
    >
        <DialogTitle>
            <Grid style={{
                minWidth: '500px',
                textAlign: 'center'
            }}>
                <Typography variant='h5' style={{
                    fontWeight: 'bold'
                }}>
                    AQI Trend
                </Typography>
            </Grid>
        </DialogTitle>
        <DialogContent>
            <Grid style={{
                overflow: 'scroll',
                minWidth: '500px'
            }}>
                {isLoading !== true ?
                    <Stack sx={{ width: '100%', color: 'grey.500' }} spacing={2}>
                        <LinearProgress color="secondary" />
                    </Stack>: dataSet?.sensorList?.map((data)=>{
                    return(
                        <AQITrendGraph 
                            sensorName={data.sensorName}
                            values={data.values}
                            labels={data.labels}
                            color={data.color}
                            status={data.status}
                        />
                    )
                })}
            </Grid>
            <Grid
                item
                xs={12}
                sm={12}
                md={12}
                lg={12}
                xl={12}
                style={{
                    display: 'inline',
                    minWidth: '500px'
                }}
                >
                <Grid style={{
                    minWidth: '500px',
                    textAlign: 'end'
                }}>
                    <Button size="medium" variant="contained"  onClick={onClose} >
                        Close
                    </Button>
                </Grid>
            </Grid>
        </DialogContent>
    </Dialog>
  )
}

export default AQITrendModal