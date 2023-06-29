import {
  Button, Dialog, DialogContent, DialogTitle, Grid, TextField, Typography,
} from '@mui/material';
import React, { useState } from 'react';
import { BackUpConfigurationservice, CompanyLogInterval } from '../../services/LoginPageService';
import ApplicationStore from '../../utils/localStorageUtil';
/* eslint-disable no-shadow */

function LogIntervalSetting({
  open, setOpen, setNotification, handleClose, intervalDetails, userRole
}) {
  const [alertLogInterval, setAlertLogInterval] = useState(intervalDetails?.alertLogInterval || '15');
  const [deviceLogInterval, setDeviceLogInterval] = useState(intervalDetails?.deviceLogInterval || '15');
  const [sensorLogInterval, setSensorLogInterval] = useState(intervalDetails?.sensorLogInterval || '15');
  const [periodicBackupInterval, setPeriodicBackupInterval] = useState(intervalDetails?.periodicBackupInterval || '60');
  const [dataRetentionPeriodInterval, setDataRetentionPeriodInterval] = useState(intervalDetails?.dataRetentionPeriodInterval || '60');
  const [expireDateReminder, setExpireDateReminder] = useState(intervalDetails?.expireDateReminder || '2');

  const handleSubmit = (e) => {
    e.preventDefault();
    userRole === 'superAdmin' ? BackUpConfigurationservice({periodicBackupInterval, dataRetentionPeriodInterval}, handleSuccess, handleException) : CompanyLogInterval({
      alertLogInterval, deviceLogInterval, sensorLogInterval, expireDateReminder,
    }, handleSuccess, handleException);
  };

  const handleException = () => { };

  const handleSuccess = (dataObject) => {
    const userDetails = ApplicationStore().getStorage('userDetails');
    ApplicationStore().setStorage('userDetails', {
      ...userDetails,
      intervalDetails: {
        alertLogInterval, deviceLogInterval, sensorLogInterval, periodicBackupInterval, dataRetentionPeriodInterval, expireDateReminder,
      },
    });
    setNotification({
      status: true,
      type: 'success',
      message: dataObject.message,
    });
    handleClose();
    setOpen(false);
  };

  const handleCancel = () => {
    setOpen(false);
    const { intervalDetails } = ApplicationStore().getStorage('userDetails');
    setAlertLogInterval(intervalDetails.alertLogInterval || '15');
    setDeviceLogInterval(intervalDetails.deviceLogInterval || '15');
    setSensorLogInterval(intervalDetails.sensorLogInterval || '15');
    setPeriodicBackupInterval(intervalDetails.periodicBackupInterval || '12');
    setDataRetentionPeriodInterval(intervalDetails.dataRetentionPeriodInterval || '12');
    setExpireDateReminder(intervalDetails.expireDateReminder || '2');
  };
  return (
    <Dialog
      fullWidth
      maxWidth="sm"
      sx={{ '& .MuiDialog-paper': { width: '95%', maxHeight: '95%' } }}
      open={open}
    >
      <DialogTitle sx={{fontFamily:'customfont', letterSpacing:'1px', fontWeight:'600', textAlign:'center', padding:'25px 0'}}>
        {userRole === 'superAdmin' ? 'Back Up Configuration' : 'Polling Interval' }
      </DialogTitle>
      <DialogContent>
        <form onSubmit={handleSubmit}>
          <Grid container spacing={1}>
            {(userRole === 'systemSpecialist' || userRole === 'Admin' || userRole === 'Manager') && <>
              <Grid container>
                <Grid
                  item
                  sx={{
                    width: '50%', textAlign: 'center', alignSelf: 'center', paddingTop: 2,
                  }}
                >
                  <Typography sx={{fontFamily:'customfont',letterSpacing:'1px'}}>
                    Alert Refresh Interval :
                  </Typography>
                </Grid>
                <Grid item sx={{ width: '50%', paddingTop: 2 }}>
                  <TextField
                    type="number"
                    fullWidth
                    md={12}
                    label="Seconds"
                    required
                    autoComplete="off"
                    value={alertLogInterval}
                    onChange={(e) => {
                      setAlertLogInterval(e.target.value);
                    }}
                    InputLabelProps={{
                      style:{fontFamily:'customfont'}
                    }}
                  />
                </Grid>
              </Grid>
              <Grid container>
                <Grid
                  item
                  sx={{
                    width: '50%', textAlign: 'center', alignSelf: 'center', paddingTop: 2,
                  }}
                >
                  <Typography sx={{fontFamily:'customfont',letterSpacing:'1px'}}>
                    Sensor Refresh Interval :
                  </Typography>
                </Grid>
                <Grid item sx={{ width: '50%', paddingTop: 2 }}>
                  <TextField
                    type="number"
                    fullWidth
                    md={12}
                    label="Seconds"
                    required
                    autoComplete="off"
                    value={sensorLogInterval}
                    onChange={(e) => {
                      setSensorLogInterval(e.target.value);
                    }}
                    InputLabelProps={{
                      style:{fontFamily:'customfont'}
                    }}
                  />
                </Grid>
              </Grid>
              <Grid container>
                <Grid
                  item
                  sx={{
                    width: '50%', textAlign: 'center', alignSelf: 'center', paddingTop: 2,
                  }}
                >
                  <Typography sx={{fontFamily:'customfont',letterSpacing:'1px'}}>
                    Device Refresh Interval :
                  </Typography>
                </Grid>
                <Grid item sx={{ width: '50%', paddingTop: 2 }}>
                  <TextField
                    type="number"
                    fullWidth
                    md={12}
                    label="Seconds"
                    required
                    autoComplete="off"
                    value={deviceLogInterval}
                    onChange={(e) => {
                      setDeviceLogInterval(e.target.value);
                    }}
                    InputLabelProps={{
                      style:{fontFamily:'customfont'}
                    }}
                  />
                </Grid>
              </Grid>
              <Grid container>
                <Grid
                  item
                  sx={{
                    width: '50%', textAlign: 'center', alignSelf: 'center', paddingTop: 2,
                  }}
                >
                  <Typography sx={{fontFamily:'customfont',letterSpacing:'1px'}}>
                    Expire Date Reminder :
                  </Typography>
                </Grid>
                <Grid item sx={{ width: '50%', paddingTop: 2 }}>
                  <TextField
                    type="number"
                    fullWidth
                    md={12}
                    label="Days"
                    required
                    autoComplete="off"
                    value={expireDateReminder}
                    onChange={(e) => {
                      setExpireDateReminder(e.target.value);
                    }}
                    InputLabelProps={{
                      style:{fontFamily:'customfont'}
                    }}
                  />
                </Grid>
              </Grid>
            </>}
            {userRole === 'superAdmin' && <>
              <Grid container>
                <Grid
                  item
                  sx={{
                    width: '50%', textAlign: 'center', alignSelf: 'center', paddingTop: 2,
                  }}
                >
                  <Typography sx={{fontFamily:'customfont',letterSpacing:'1px'}}>
                    Backup :
                  </Typography>
                </Grid>
                <Grid item sx={{ width: '50%', paddingTop: 2 }}>
                  <TextField
                    type="number"
                    fullWidth
                    md={12}
                    label="Days"
                    required
                    autoComplete="off"
                    value={periodicBackupInterval}
                    onChange={(e) => {
                      setPeriodicBackupInterval(e.target.value);
                    }}
                    InputLabelProps={{
                      style:{fontFamily:'customfont'}
                    }}
                  />
                </Grid>
              </Grid>
              <Grid container>
                <Grid
                  item
                  sx={{
                    width: '50%', textAlign: 'center', alignSelf: 'center', paddingTop: 2,
                  }}
                >
                  <Typography sx={{fontFamily:'customfont',letterSpacing:'1px'}}>
                    Data Retention Period :
                  </Typography>
                </Grid>
                <Grid item sx={{ width: '50%', paddingTop: 2 }}>
                  <TextField
                    type="number"
                    fullWidth
                    md={12}
                    label="Days"
                    required
                    autoComplete="off"
                    value={dataRetentionPeriodInterval}
                    onChange={(e) => {
                      setDataRetentionPeriodInterval(e.target.value);
                    }}
                    InputLabelProps={{
                      style:{fontFamily:'customfont'}
                    }}
                  />
                </Grid>
              </Grid>
            </>
            }
            <Grid
              container
              fullWidth
              style={{
                flexFlow: 'row-reverse',
                marginTop:'15px'
              }}
            >
              {(userRole === 'systemSpecialist' || userRole === 'superAdmin') && 
                <Button type="submit"
                style={{
                  background: 'rgb(19 60 129)',}}
                sx={{
                  m: 1,
                  height: '40px',
                  color: 'white',
                  padding: '8px 19px',
                  marginTop: '10px',
                  marginRight: '10px',
                  marginBottom: '35px',
                  bordeRadius: '10px',
                  fontWeight: '600',
                  fontFamily: 'customfont',
                  letterSpacing: '1px'
                }}>
                  Update
                </Button>
              }
              <Button onClick={handleCancel}
               style={{
                background: 'rgb(19 60 129)',}}
              sx={{
                m: 1,
                height: '40px',
                color: 'white',
                padding: '8px 19px',
                marginTop: '10px',
                marginRight: '10px',
                marginBottom: '35px',
                bordeRadius: '10px',
                fontWeight: '600',
                fontFamily: 'customfont',
                letterSpacing: '1px'
              }}>
                Cancel
              </Button>
            </Grid>
          </Grid>
        </form>
      </DialogContent>
    </Dialog>
  );
}

export default LogIntervalSetting;
