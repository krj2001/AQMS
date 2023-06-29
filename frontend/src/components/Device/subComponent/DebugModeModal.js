import { Check, Close } from '@mui/icons-material';
import {
  Button, CircularProgress, Dialog, DialogContent, DialogTitle, Fab,
} from '@mui/material';
import { green, red } from '@mui/material/colors';
import React, { useEffect, useState } from 'react';
import { ChangeDeviceMode, DeviceDebugResultService } from '../../../services/LoginPageService';

function DebugModeModal({
  open, setOpen, setRefreshData, device_id, setNotification, changeDeviceId, changeDeviceIdMode, setChangeDeviceId
}) {
  const [debugStatus, setDebugStatus] = useState(false);
  const [rtc, setRtc] = useState('NA');
  const [sdCardStatus, setSdCardStatus] = useState(false);
  const [networkStatus, setNetworkStatus] = useState('NA');

  useEffect(() => {
    if(changeDeviceId){
      if(changeDeviceIdMode === 'debug'){
        setTimeout(()=>{
          DeviceDebugResultService({device_id: device_id}, handleDebugSuccess, handleDebugException);
        }, 9000);
      }
    }
  }, [changeDeviceId]);

  const buttonColor = (statusVariable) =>{
    return {
      ...({
        bgcolor: statusVariable ? green[500] : red[500],
        '&:hover': {
          bgcolor: statusVariable ? green[700] : red[700],
        },
      }),
    }
  };


  const handleDebugSuccess = (dataObject) =>{
    setDebugStatus(true);
    if(dataObject.data[0]){
      setRtc(dataObject.data[0].RTC);
      setNetworkStatus(dataObject.data[0].RSSI);
      setSdCardStatus(()=>{
        return dataObject.data[0].sdCard === '1' ? true : false
      });
    }
  }

  const handleDebugException = (errorObject, errorMessage) => {
    console.log(errorObject+ '' + errorMessage);
    setDebugStatus(true);
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
  };

  const onCancel = () => {
    setChangeDeviceId('');
    setDebugStatus(false);
    setRtc('NA');
    setSdCardStatus(false);
    setNetworkStatus('NA');
    ChangeDeviceMode({ id: device_id, deviceMode: 'enabled' }, modeHandleSuccess, modeChangeHandleException);
  };

  const modeHandleSuccess = () => {
    setRefreshData((oldvalue) => !oldvalue);
    setOpen(false);
  };

  const modeChangeHandleException = () => { };
  return (
    <Dialog
      fullWidth
      maxWidth="sm"
      sx={{ '& .MuiDialog-paper': { width: '100%' } }}
      open={open}
    >
      <DialogTitle>
        Debugging...
      </DialogTitle>
      <DialogContent>
        <div style={{
          minHeight: '30px',
          display: 'flex',
        }}>
          <div style={{
            display: 'flex',
            justifyContent: 'space-between',
            minWidth: '150px'
          }}>
            <h6>
              Network status 
            </h6>
            : &nbsp;
          </div>
          {!debugStatus
            ? (
              <CircularProgress
                size={26}
                sx={{
                  color: green[500],
                }}
              />
            )
            : (<>
              {networkStatus}
            </>
            )}

        </div>
        <div style={{
          minHeight: '30px',
          display: 'flex',
        }}>
          <div style={{
            display: 'flex',
            justifyContent: 'space-between',
            minWidth: '150px'
          }}>
            <h6>
              RTC
            </h6>
            : &nbsp;
          </div>
            {!debugStatus
              ? (
                <CircularProgress
                  size={26}
                  sx={{
                    color: green[500],
                  }}
                />
              )
              : (<>
                {rtc}
              </>
              )}
        </div>
        <div style={{
          display: 'flex',
          minHeight: '30px'
        }}>
          <div style={{
            display: 'flex',
            justifyContent: 'space-between',
            minWidth: '150px'
          }}>
            <h6>
              SD Card Status 
            </h6>
            : &nbsp;
          </div>
          {!debugStatus
            ? (
              <CircularProgress
                size={26}
                sx={{
                  color: green[500],
                }}
              />
            )
            : (
              <Fab
                aria-label="save"
                color='primary'
                sx={buttonColor(sdCardStatus)}
                style={{
                  height: 25,
                  width: 25,
                  minHeight: 25,
                }}
              >
                {sdCardStatus
                  ? 
                  <Check fontSize="24px" /> :
                  <Close fontSize="24px" />
                  }
              </Fab>
            )}

        </div>
        <div className="float-right">
          <div className="rounded-md -space-y-px">
            <Button
              sx={{ m: 2 }}
              onClick={onCancel}
            >
              Done
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
}

export default DebugModeModal;
