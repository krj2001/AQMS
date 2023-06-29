import { Check, Close } from '@mui/icons-material';
import { Alert, Box, Chip, CircularProgress, Dialog, DialogTitle, Fab, Grid, LinearProgress } from '@mui/material'
import { green, red } from '@mui/material/colors';
import React, { useEffect, useState } from 'react'
import { ConfigUpgradeModeResult, TestModeChangeInitiator } from '../../../services/LoginPageService';

const DeviceModeModal = ({
  changeDeviceId, setChangeDeviceId, changeDeviceIdMode, deviceModalOpen, setDeviceModalOpen, deviceModeHeader, deviceModeSubHeader, setNotification, setRefreshData
}) => {
  const [isInitialProgress, setIsInitialProgress] = useState(true);
  const [isSecondaryProgress, setIsSecondaryProgress] = useState(false);
  const [isResult, setIsResult] = useState(false);
  const [resultStatus, setResultStatus] = useState(true);
  const buttonSx = {
    ...({
      bgcolor: resultStatus === true ? green[500] : red[500],
      '&:hover': {
        bgcolor: resultStatus === true ? green[700] : red[700],
      },
    }),
  };
  var primaryInterval;
  var secondaryInterval;
  
  useEffect(()=>{
    if(deviceModalOpen === true){
      if(changeDeviceId){
        if(changeDeviceIdMode === 'config' || changeDeviceIdMode === 'firmwareUpgradation'){
          primaryInterval = setInterval(()=>{
            if(isInitialProgress === true){
              TestModeChangeInitiator({ device_id : changeDeviceId }, modeChangeInitiatorHandleSuccess, modeChangeInitiatorHandleException);
              console.log('Initial call..');
              console.log('Device Id'+ changeDeviceId);
            }
            else{
              clearInterval(primaryInterval);
            }
          }, 5000);
          return () => clearInterval(primaryInterval);
        }
      }
    }
  },[changeDeviceId, deviceModalOpen]);

  const configUpgradeAPI = () =>{
    if(changeDeviceId){
      if(changeDeviceIdMode === 'config' || 'firmwareUpgradation'){
        secondaryInterval = setInterval(()=>{
          if(isResult === false){
            ConfigUpgradeModeResult({device_id : changeDeviceId}, configUpgradeHandleSuccess, configUpgradeHandleException);
            console.log('Secondary call...');
          }
          else {
            clearInterval(secondaryInterval);
            setChangeDeviceId('');
          }
        }, 10000);
        return () => clearInterval(secondaryInterval);
      }
    }
  };

  const configUpgradeHandleSuccess = (dataObject) => {
    if(dataObject.configComplete === 1){
      clearInterval(secondaryInterval);
      setTimeout(()=>{
        setIsResult(true);
        setResultStatus(true);
        setChangeDeviceId('');
        setTimeout(()=>{
          setIsResult(false);
          setIsInitialProgress(true);
          setIsSecondaryProgress(false);
          setDeviceModalOpen(false);
          setNotification({
            status: true,
            type: 'success',
            message: dataObject.message,
          });
          setRefreshData(oldValue => !oldValue);
        },3000);
      }, 3000);
    }
    else if(dataObject.configComplete === 0){
      clearInterval(secondaryInterval);
      setTimeout(()=>{
        setResultStatus(false);
        setIsResult(true);
        setChangeDeviceId('');
        setTimeout(()=>{
          setIsResult(false);
          setIsInitialProgress(true);
          setIsSecondaryProgress(false);
          setDeviceModalOpen(false);
          setNotification({
            status: true,
            type: 'success',
            message: dataObject.message,
          });
        },3000);
      }, 3000);
    } else {
      //
    }
  }

  const configUpgradeHandleException = (errorObject) => {
    //
  }
  const modeChangeInitiatorHandleSuccess = (dataObject) =>{
    if(dataObject.isInitialProgress === 1){
      clearInterval(primaryInterval);
      setTimeout(()=>{
        setIsInitialProgress(false);
        setIsSecondaryProgress(true);
        setIsResult(false);
        setNotification({
          status: true,
          type: 'success',
          message: dataObject.message,
        });
        configUpgradeAPI();
      },3000);
    }
  }

  const modeChangeInitiatorHandleException = (errorObject, errormessage) =>{
    setDeviceModalOpen(false);
  }
  

  return (
    <Dialog
    fullWidth
    maxWidth="sm"
    sx={{ '& .MuiDialog-paper': { width: '100%' } }}
    open={deviceModalOpen}
  >
      <Grid>
        <DialogTitle style={{padding: '0px', paddingLeft: '26px', paddingTop: '10px'}}>
          {deviceModeHeader}
            
          <div style={{
              float: 'right',
              paddingRight: '5px',
              fontSize: 'medium',
              fontWeight: 'normal',
              display: 'inline'
            }}>
              <div style={{
                marginRight: 2,
                display: 'inline'
              }}>
                Device Status : 
              </div>
              <Chip
                variant="outlined"
                label="Connected"
                style={{
                  color: 'green',
                  borderColor: 'green',
                }}
              />
            </div>
        </DialogTitle>
        <Grid container spacing={1} sx={{ p: 3}}>
          <Box style={{
            textAlign: 'center',
            width: '100%'
          }}>
            <div style={{
              textAlign: 'center',
              width: '100%'
            }}>
              <Alert 
                // icon={false} 
                severity="warning" style={{
                textAlign: 'center',
                width: '100%',
              }}>
                
                Do not turn off the device and make sure that you have a stable network
              </Alert>
            </div>
            
          </Box>
          {isInitialProgress === true && 
            <Grid style={{ 
              width: '100%', 
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'center',
              marginTop: 25
            }}>
                <div>
                    Checking Connection...
                </div>
                <div style={{ width: '100%', marginTop: 1}}>
                    <div style={{ width: '100%', marginTop: 5, textAlignLast: 'center' }}>
                        <CircularProgress />
                    </div>
                </div>
            </Grid>
          }
          {isSecondaryProgress === true && 
            <Grid style={{ 
                width: '100%', 
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                marginTop: 25
            }}>
                <div>
                    {deviceModeSubHeader}
                </div>
                <div style={{ width: '100%', marginTop: 1}}>
                    <div style={{ width: '100%', marginTop: 5, textAlign: 'center' }}>
                      {isResult === false ? <LinearProgress /> :
                        <Fab
                          color={resultStatus === true ? 'primary' : 'secondary'}
                          sx={buttonSx}
                          style={{
                            height: 50,
                            width: 50,
                            minHeight: 50,
                          }}
                        >
                          {resultStatus === true ? 
                            <Check style={{ fontSize: '30px' }}/> :
                            <Close style={{ fontSize: '30px' }} /> 
                          }
                        </Fab>
                      }
                    </div>
                </div>
            </Grid>
          }
        </Grid>
      </Grid>
  </Dialog>
  )
}

export default DeviceModeModal