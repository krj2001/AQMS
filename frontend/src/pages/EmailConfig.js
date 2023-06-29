import { Box, Button, Grid, Tab, Tabs, TextField, Typography } from '@mui/material'
import React, { useEffect, useState } from 'react'
import EmailConfigToolBar from '../components/emailTemplateConfig/EmailConfigToolBar';
import MessageConfigToolBar from '../components/emailTemplateConfig/MessageConfigToolBar'
import NotificationBar from '../components/notification/ServiceNotificationBar';
import { EmailTemplateFetchService, EmailTemplateUpdateService } from '../services/LoginPageService';

const EmailConfig = () => {
  const [refreshData, setRefreshData] = useState(false);
  const [isEdit, setisEdit] = useState(true);

  const [id, setId] = useState('');
  const [calibrartionSubject, setCalibrartionSubject] = useState('');
  const [calibrartionBody, setCalibrartionBody] = useState('');
  const [bumpTestSubject, setBumpTestSubject] = useState('');
  const [bumpTestBody, setBumpTestBody] = useState('');
  const [stelSubject, setStelSubject] = useState('');
  const [stelBody, setStelBody] = useState('');
  const [twaSubject, setTwaSubject] = useState('');
  const [twaBody, setTwaBody] = useState('');
  const [warningSubject, setWarningSubject] = useState('');
  const [warningBody, setWarningBody] = useState('');
  const [criticalSubject, setCriticalSubject] = useState('');
  const [criticalBody, setCriticalBody] = useState('');
  const [outOfRangeSubject, setOutOfRangeSubject] = useState('');
  const [outOfRangeBody, setOutOfRangeBody] = useState('');
  const [periodicitySubject, setPeriodicitySubject] = useState('');
  const [periodicityBody, setPeriodicityBody] = useState('');
  const [value, setValue] = useState(0);

  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  useEffect(()=>{
    EmailTemplateFetchService(handleFetchSuccess, handleFetchException);
  }, [refreshData]);
  
  const handleChange = (event, newValue) => {
    setValue(newValue);
    onCancel();
  };

  function a11yProps(index) {
    return {
      id: `simple-tab-${index}`,
      'aria-controls': `simple-tabpanel-${index}`,
    };
  }

  const handleFetchSuccess = (dataObject) =>{
    console.log(dataObject);
    setId(dataObject.data[0]?.id || '');
    setCalibrartionSubject(dataObject.data[0]?.calibrartionSubject || '');
    setCalibrartionBody(dataObject.data[0]?.calibrartionBody || '');
    setBumpTestSubject(dataObject.data[0]?.bumpTestSubject || '');
    setBumpTestBody(dataObject.data[0]?.bumpTestBody || '');
    setStelSubject(dataObject.data[0]?.stelSubject || '');
    setStelBody(dataObject.data[0]?.stelBody || '');
    setTwaSubject(dataObject.data[0]?.twaSubject || '');
    setTwaBody(dataObject.data[0]?.twaBody || '');
    setWarningSubject(dataObject.data[0]?.warningSubject || '');
    setWarningBody(dataObject.data[0]?.warningBody || '');
    setCriticalSubject(dataObject.data[0]?.criticalSubject || '');
    setCriticalBody(dataObject.data[0]?.criticalBody || '');
    setOutOfRangeSubject(dataObject.data[0]?.outOfRangeSubject || '');
    setOutOfRangeBody(dataObject.data[0]?.outOfRangeBody || '');
    setPeriodicitySubject(dataObject.data[0]?.periodicitySubject || '');
    setPeriodicityBody(dataObject.data[0]?.periodicityBody || '');
  }

  const handleFetchException = (errorObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
  };
  const onSubmit = (e) =>{
    e.preventDefault();
    EmailTemplateUpdateService({
      id,
      calibrartionSubject,
      calibrartionBody,
      bumpTestSubject,
      bumpTestBody,
      stelSubject,
      stelBody,
      twaSubject,
      twaBody,
      warningSubject,
      warningBody,
      criticalSubject,
      criticalBody,
      outOfRangeSubject,
      outOfRangeBody,
      periodicitySubject,
      periodicityBody
    }, handleUpdateSuccess, handleUpdateException);
  }

  const handleUpdateSuccess = (dataObject) => {
    setisEdit(true);
    setRefreshData(oldValue=>!oldValue);
    setNotification({
      status: true,
      type: 'success',
      message: dataObject.message,
    });
  };

  const handleUpdateException = (errorObject, errorMessage) => {
    setisEdit(true);
    setRefreshData(oldValue=>!oldValue);
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
  };

  const handleClose = () => {
    setNotification({
      status: false,
      type: '',
      message: '',
    });
  };

  const onCancel = () =>{
    setisEdit(true);
    setRefreshData(oldValue=>!oldValue);
  }

  function TabPanel(props) {
    const { children, value, index, ...other } = props;
  
    return (
      <div
        role="tabpanel"
        hidden={value !== index}
        id={`simple-tabpanel-${index}`}
        aria-labelledby={`simple-tab-${index}`}
        {...other}
      >
        {value === index && (
          <Box sx={{ p: 3 }} style={{padding: '5px', paddingTop: '0px'}}>
            <Typography>{children}</Typography>
          </Box>
        )}
      </div>
    );
  }

  return (
    <Grid container className='h-[auto] sm:h-[87vh] bg-white mt-4  rounded-xl ml-auto mr-auto  p-3 sm:p-10' style={{ width: '90%',boxShadow: 'rgba(100, 100, 111, 0.2) 0px 7px 29px 0px' }}>
    <Box className='w-11/12 ml-auto mr-auto '>
      <Box>
        <Tabs value={value} onChange={handleChange} aria-label="basic tabs example" className='w-full' sx={{ width: 'auto', }}
          variant='scrollable'
          visibleScrollbar={true}>
          <Tab label="Email Template" style={{ fontFamily: 'customfont', fontWeight: '600', letterSpacing: '1px' }} {...a11yProps(0)} />
          <Tab label="Message Template" style={{ fontFamily: 'customfont', fontWeight: '600', letterSpacing: '1px' }} {...a11yProps(1)} />

        </Tabs>
        <EmailConfigToolBar isEdit={isEdit} setisEdit={setisEdit} />

      </Box>
    </Box>
    {/* <TabPanel value={value} index={0}>
      <EmailConfigToolBar isEdit={isEdit} setisEdit={setisEdit}/>
      <form onSubmit={onSubmit}>
        <Grid container>
          <Grid container spacing={1} style={{
            padding:'2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              Calibration :
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Calibration Subject"
                type="text"
                disabled={isEdit}
                value={calibrartionSubject}
                variant="outlined"
                onChange={(e) => { setCalibrartionSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Calibration Body"
                type="text"
                disabled={isEdit}
                value={calibrartionBody}
                variant="outlined"
                onChange={(e) => { setCalibrartionBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} style={{
            padding:'2px',
            marginTop: '2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              Bump Test :
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Bump Test Subject"
                type="text"
                disabled={isEdit}
                value={bumpTestSubject}
                variant="outlined"
                onChange={(e) => { setBumpTestSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Bump Test Body"
                type="text"
                disabled={isEdit}
                value={bumpTestBody}
                variant="outlined"
                onChange={(e) => { setBumpTestBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} style={{
            padding:'2px',
            marginTop: '2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              STEL :
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="STEL Subject"
                type="text"
                disabled={isEdit}
                value={stelSubject}
                variant="outlined"
                onChange={(e) => { setStelSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="STEL Body"
                type="text"
                disabled={isEdit}
                value={stelBody}
                variant="outlined"
                onChange={(e) => { setStelBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} style={{
            padding:'2px',
            marginTop: '2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              TWA :
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="TWA Subject"
                type="text"
                disabled={isEdit}
                value={twaSubject}
                variant="outlined"
                onChange={(e) => { setTwaSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="TWA Body"
                type="text"
                disabled={isEdit}
                value={twaBody}
                variant="outlined"
                onChange={(e) => { setTwaBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} style={{
            padding:'2px',
            marginTop: '2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              Warning :
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Warning Subject"
                type="text"
                disabled={isEdit}
                value={warningSubject}
                variant="outlined"
                onChange={(e) => { warningSubject, setWarningSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Warning Body"
                type="text"
                disabled={isEdit}
                value={warningBody}
                variant="outlined"
                onChange={(e) => { setWarningBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} style={{
            padding:'2px',
            marginTop: '2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              Critical :
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Critical Subject"
                type="text"
                disabled={isEdit}
                value={criticalSubject}
                variant="outlined"
                onChange={(e) => { setCriticalSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Critical Body"
                type="text"
                disabled={isEdit}
                value={criticalBody}
                variant="outlined"
                onChange={(e) => { setCriticalBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} style={{
            padding:'2px',
            marginTop: '2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              Out of Range:
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Out of Range Subject"
                type="text"
                disabled={isEdit}
                value={outOfRangeSubject}
                variant="outlined"
                onChange={(e) => { setOutOfRangeSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Out of Range Body"
                type="text"
                disabled={isEdit}
                value={outOfRangeBody}
                variant="outlined"
                onChange={(e) => { setOutOfRangeBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} style={{
            padding:'2px',
            marginTop: '2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              Periodicity :
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Periodicity Subject"
                type="text"
                disabled={isEdit}
                value={periodicitySubject}
                variant="outlined"
                onChange={(e) => { setPeriodicitySubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Periodicity Body"
                type="text"
                disabled={isEdit}
                value={periodicityBody}
                variant="outlined"
                onChange={(e) => { setPeriodicityBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
        </Grid>
        <Grid container style={{
          display: 'block',
            // float: 'right'
          }}>
          <Grid item style={{
            float: 'right'
          }}>
            <Button
              type="submit"
              disabled={isEdit}
            >
              Update
            </Button>
            <Button
              onClick={onCancel}
            >
              Cancel
            </Button>
          </Grid>
        </Grid>
      </form>
    </TabPanel>
    <TabPanel value={value} index={1}>
    <MessageConfigToolBar isEdit={isEdit} setisEdit={setisEdit}/>
      <form onSubmit={onSubmit}>
        <Grid container>
          <Grid container spacing={1} style={{
            padding:'2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              Calibration :
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Calibration Subject"
                type="text"
                disabled={isEdit}
                value={calibrartionSubject}
                variant="outlined"
                onChange={(e) => { setCalibrartionSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Calibration Body"
                type="text"
                disabled={isEdit}
                value={calibrartionBody}
                variant="outlined"
                onChange={(e) => { setCalibrartionBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} style={{
            padding:'2px',
            marginTop: '2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              Bump Test :
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Bump Test Subject"
                type="text"
                disabled={isEdit}
                value={bumpTestSubject}
                variant="outlined"
                onChange={(e) => { setBumpTestSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Bump Test Body"
                type="text"
                disabled={isEdit}
                value={bumpTestBody}
                variant="outlined"
                onChange={(e) => { setBumpTestBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} style={{
            padding:'2px',
            marginTop: '2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              STEL :
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="STEL Subject"
                type="text"
                disabled={isEdit}
                value={stelSubject}
                variant="outlined"
                onChange={(e) => { setStelSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="STEL Body"
                type="text"
                disabled={isEdit}
                value={stelBody}
                variant="outlined"
                onChange={(e) => { setStelBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} style={{
            padding:'2px',
            marginTop: '2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              TWA :
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="TWA Subject"
                type="text"
                disabled={isEdit}
                value={twaSubject}
                variant="outlined"
                onChange={(e) => { setTwaSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="TWA Body"
                type="text"
                disabled={isEdit}
                value={twaBody}
                variant="outlined"
                onChange={(e) => { setTwaBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} style={{
            padding:'2px',
            marginTop: '2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              Warning :
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Warning Subject"
                type="text"
                disabled={isEdit}
                value={warningSubject}
                variant="outlined"
                onChange={(e) => { warningSubject, setWarningSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Warning Body"
                type="text"
                disabled={isEdit}
                value={warningBody}
                variant="outlined"
                onChange={(e) => { setWarningBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} style={{
            padding:'2px',
            marginTop: '2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              Critical :
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Critical Subject"
                type="text"
                disabled={isEdit}
                value={criticalSubject}
                variant="outlined"
                onChange={(e) => { setCriticalSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Critical Body"
                type="text"
                disabled={isEdit}
                value={criticalBody}
                variant="outlined"
                onChange={(e) => { setCriticalBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} style={{
            padding:'2px',
            marginTop: '2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              Out of Range:
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Out of Range Subject"
                type="text"
                disabled={isEdit}
                value={outOfRangeSubject}
                variant="outlined"
                onChange={(e) => { setOutOfRangeSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}
            >
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Out of Range Body"
                type="text"
                disabled={isEdit}
                value={outOfRangeBody}
                variant="outlined"
                onChange={(e) => { setOutOfRangeBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} style={{
            padding:'2px',
            marginTop: '2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              Periodicity :
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}
            >
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Periodicity Subject"
                type="text"
                disabled={isEdit}
                value={periodicitySubject}
                variant="outlined"
                onChange={(e) => { setPeriodicitySubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}
            >
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Periodicity Body"
                type="text"
                disabled={isEdit}
                value={periodicityBody}
                variant="outlined"
                onChange={(e) => { setPeriodicityBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
        </Grid>
        <Grid container style={{
          display: 'block',
            // float: 'right'
          }}>
          <Grid item style={{
            float: 'right'
          }}>
            <Button
              type="submit"
              disabled={isEdit}
            >
              Update
            </Button>
            <Button
              onClick={onCancel}
            >
              Cancel
            </Button>
          </Grid>
        </Grid>
      </form>
    </TabPanel> */}
    {value === 0 && <>
      {/* <EmailConfigToolBar isEdit={isEdit} setisEdit={setisEdit} /> */}
      <form onSubmit={onSubmit} className=' py-5 bg-white w-full rounded-xl ml-auto mr-auto overflow-auto h-[60vh] px-0 sm:px-10'>
        {/* <EmailConfigToolBar isEdit={isEdit} setisEdit={setisEdit} /> */}
        <Grid container className='mt-0'>
          <Grid container spacing={1} className='p-1 min-[320px]:flex-col' sx={{ justifyContent: 'center' }}>
            <Grid item xs={12} sm={12} md={5} lg={5} xl={5} className='mb-2'>
              <Grid className='ml-6 mb-1 mt-3 text-sm text-slate-500 text-left font-[customfont] tracking-[1px]'>Calibration Subject</Grid>
              <TextField
                type="text"
                disabled={isEdit}
                value={calibrartionSubject}
                className='w-11/12 h-1 text-xs'
                variant="outlined"
                size='small'
                onChange={(e) => { setCalibrartionSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
                InputProps={{
                  className: 'bg-[#f9f9f9] '
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <Grid className='ml-6 mt-3 mb-1 text-sm text-slate-500 text-left font-[customfont] tracking-[1px]'>Calibration Body</Grid>
              <TextField
                type="text"
                disabled={isEdit}
                value={calibrartionBody}
                size='small'
                className='w-11/12 '
                variant="outlined"
                onChange={(e) => { setCalibrartionBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
                InputProps={{
                  className: 'bg-[#f9f9f9] '
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} className={'p-1 mt-0.5'}
            sx={{ justifyContent: 'center' }}
          >
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <Grid className='ml-6 mb-1 mt-3 text-sm text-slate-500 text-left font-[customfont] tracking-[1px]'>Bump Test Subject</Grid>
              <TextField
                type="text"
                disabled={isEdit}
                size='small'
                value={bumpTestSubject}
                className='w-11/12 '
                variant="outlined"
                onChange={(e) => { setBumpTestSubject(e.target.value); }}
                autoComplete="off"
                InputProps={{
                  className: 'bg-[#f9f9f9] '
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <Grid className='ml-6 mb-1 mt-3 text-sm text-slate-500 text-left font-[customfont] tracking-[1px]'>Bump Test Body</Grid>
              <TextField
                type="text"
                disabled={isEdit}
                value={bumpTestBody}
                size='small'
                className='w-11/12 '
                variant="outlined"
                onChange={(e) => { setBumpTestBody(e.target.value); }}
                autoComplete="off"
                InputProps={{
                  className: 'bg-[#f9f9f9] '
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} className={'p-1 mt-0.5'}
            sx={{ justifyContent: 'center' }}
          >
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <Grid className='ml-6 mb-1 mt-3 text-sm text-slate-500 text-left font-[customfont] tracking-[1px]'>STEL Subject</Grid>
              <TextField
                type="text"
                disabled={isEdit}
                value={stelSubject}
                className='w-11/12 '
                size='small'
                variant="outlined"
                onChange={(e) => { setStelSubject(e.target.value); }}
                autoComplete="off"
                InputProps={{
                  className: 'bg-[#f9f9f9] '
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <Grid className='ml-6 mb-1 mt-3 text-sm text-slate-500 text-left font-[customfont] tracking-[1px]'>STEL Body</Grid>
              <TextField
                type="text"
                disabled={isEdit}
                value={stelBody}
                size='small'
                className='w-11/12 '
                variant="outlined"
                onChange={(e) => { setStelBody(e.target.value); }}
                autoComplete="off"
                InputProps={{
                  className: 'bg-[#f9f9f9] '
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} className={'p-1 mt-0.5'}
            sx={{ justifyContent: 'center' }}
          >
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <Grid className='ml-6 mb-1 mt-3 text-sm text-slate-500 text-left font-[customfont] tracking-[1px]'>TWA Subject</Grid>
              <TextField
                type="text"
                disabled={isEdit}
                value={twaSubject}
                size='small'
                className='w-11/12 '
                variant="outlined"
                onChange={(e) => { setTwaSubject(e.target.value); }}
                autoComplete="off"
                InputProps={{
                  className: 'bg-[#f9f9f9] '
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <Grid className='ml-6 mb-1 mt-3 text-sm text-slate-500 text-left font-[customfont] tracking-[1px]'>TWA Body</Grid>
              <TextField
                type="text"
                disabled={isEdit}
                value={twaBody}
                className='w-11/12 '
                size='small'
                variant="outlined"
                onChange={(e) => { setTwaBody(e.target.value); }}
                autoComplete="off"
                InputProps={{
                  className: 'bg-[#f9f9f9] '
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} className={'p-1 mt-0.5'}
            sx={{ justifyContent: 'center' }}
          >
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <Grid className='ml-6 mb-1 mt-3 text-sm text-slate-500 text-left font-[customfont] tracking-[1px]'>Warning Subject</Grid>
              <TextField
                type="text"
                disabled={isEdit}
                value={warningSubject}
                className='w-11/12 '
                size='small'
                variant="outlined"
                onChange={(e) => { warningSubject, setWarningSubject(e.target.value); }}
                autoComplete="off"
                InputProps={{
                  className: 'bg-[#f9f9f9] '
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <Grid className='ml-6 mb-1 mt-3 text-sm text-slate-500 text-left font-[customfont] tracking-[1px]'>Warning Body</Grid>
              <TextField
                type="text"
                disabled={isEdit}
                value={warningBody}
                className='w-11/12 '
                size='small'
                variant="outlined"
                onChange={(e) => { setWarningBody(e.target.value); }}
                autoComplete="off"
                InputProps={{
                  className: 'bg-[#f9f9f9] '
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} className={'p-1 mt-0.5'}
            sx={{ justifyContent: 'center' }}
          >
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <Grid className='ml-6 mb-1 mt-3 text-sm text-slate-500 text-left font-[customfont] tracking-[1px]'>Critical Subject</Grid>
              <TextField
                type="text"
                disabled={isEdit}
                value={criticalSubject}
                size='small'
                className='w-11/12 '
                variant="outlined"
                onChange={(e) => { setCriticalSubject(e.target.value); }}
                autoComplete="off"
                InputProps={{
                  className: 'bg-[#f9f9f9]'
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <Grid className='ml-6 mb-1 mt-3 text-sm text-slate-500 text-left font-[customfont] tracking-[1px]'>Critical Body</Grid>
              <TextField
                type="text"
                disabled={isEdit}
                value={criticalBody}
                size='small'
                className='w-11/12 '
                variant="outlined"
                onChange={(e) => { setCriticalBody(e.target.value); }}
                autoComplete="off"
                InputProps={{
                  className: 'bg-[#f9f9f9] '
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} className={'p-1 mt-0.5'}
            sx={{ justifyContent: 'center' }}
          >
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <Grid className='ml-6 mb-1 mt-3 text-sm text-slate-500 text-left font-[customfont] tracking-[1px]'>Out of Range Subject</Grid>
              <TextField
                type="text"
                disabled={isEdit}
                value={outOfRangeSubject}
                size='small'
                className='w-11/12 '
                variant="outlined"
                onChange={(e) => { setOutOfRangeSubject(e.target.value); }}
                autoComplete="off"
                InputProps={{
                  className: 'bg-[#f9f9f9] '
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <Grid className='ml-6 mb-1 mt-3 text-sm text-slate-500 text-left font-[customfont]  tracking-[1px]'>Out of Range Body</Grid>
              <TextField
                type="text"
                disabled={isEdit}
                value={outOfRangeBody}
                size='small'
                className='w-11/12 '
                variant="outlined"
                onChange={(e) => { setOutOfRangeBody(e.target.value); }}
                autoComplete="off"
                InputProps={{
                  className: 'bg-[#f9f9f9] '
                }}
              />
            </Grid>
          </Grid>

          <Grid container spacing={1} className={'p-1 mt-0.5'}
            sx={{ justifyContent: 'center' }}
          >
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <Grid className='ml-6 mb-1 mt-3 text-sm text-slate-500 text-left font-[customfont]  tracking-[1px]'>Periodicity Subject</Grid>
              <TextField
                type="text"
                disabled={isEdit}
                value={periodicitySubject}
                size='small'
                className='w-11/12 '
                variant="outlined"
                onChange={(e) => { setPeriodicitySubject(e.target.value); }}
                autoComplete="off"
                InputProps={{
                  className: 'bg-[#f9f9f9] '
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <Grid className='ml-6 mb-1 mt-3 text-sm text-slate-500 text-left font-[customfont] tracking-[1px]'>Periodicity Body</Grid>
              <TextField
                type="text"
                disabled={isEdit}
                value={periodicityBody}
                size='small'
                className='w-11/12 '
                variant="outlined"
                onChange={(e) => { setPeriodicityBody(e.target.value); }}
                autoComplete="off"
                InputProps={{
                  className: 'inputfield bg-[#f9f9f9]',
                }}
              />
            </Grid>
          </Grid>
        </Grid>

      </form>
      <Grid container className='w-10/12 ml-auto mr-auto' style={{ display: 'block', width: '90%' }}>
        <Grid item className={'float-right'}>
          <Button
            type="submit"
            disabled={isEdit}
            style={{
              background: 'rgb(19 60 129)',}}
            // style={{color:'white'}}  
            sx={{
              height: '0',
              color: 'white',
              padding: "10px 19px",
              fontSize: '13px',
              borderRadius: '10px',
              fontWeight: '600',
              fontFamily: 'customfont',
              letterSpacing: '1px',
              boxShadow: 'none',
              marginRight: '30px',
              "&.Mui-disabled": {
                background: "#eaeaea",
                color: "#c0c0c0"
              }
            }}
          >
            Update
          </Button>
          <Button
            onClick={onCancel}
            style={{
              background: 'rgb(19 60 129)',}}
            sx={{
              height: '0',
              color: 'white',
              padding: "10px 19px",
              fontSize: '13px',
              borderRadius: '10px',
              fontWeight: '600',
              fontFamily: 'customfont',
              letterSpacing: '1px',
              boxShadow: 'none'
            }}
          >
            Cancel
          </Button>
        </Grid>
      </Grid>
    </>}
    {value === 1 && <>
      {/* <MessageConfigToolBar isEdit={isEdit} setisEdit={setisEdit}/> */}
      {/* <form onSubmit={onSubmit}>
        <Grid container>
          <Grid container spacing={1} style={{
            padding:'2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              Calibration :
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Calibration Subject"
                type="text"
                disabled={isEdit}
                value={calibrartionSubject}
                variant="outlined"
                onChange={(e) => { setCalibrartionSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Calibration Body"
                type="text"
                disabled={isEdit}
                value={calibrartionBody}
                variant="outlined"
                onChange={(e) => { setCalibrartionBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} style={{
            padding:'2px',
            marginTop: '2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              Bump Test :
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Bump Test Subject"
                type="text"
                disabled={isEdit}
                value={bumpTestSubject}
                variant="outlined"
                onChange={(e) => { setBumpTestSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Bump Test Body"
                type="text"
                disabled={isEdit}
                value={bumpTestBody}
                variant="outlined"
                onChange={(e) => { setBumpTestBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} style={{
            padding:'2px',
            marginTop: '2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              STEL :
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="STEL Subject"
                type="text"
                disabled={isEdit}
                value={stelSubject}
                variant="outlined"
                onChange={(e) => { setStelSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="STEL Body"
                type="text"
                disabled={isEdit}
                value={stelBody}
                variant="outlined"
                onChange={(e) => { setStelBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} style={{
            padding:'2px',
            marginTop: '2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              TWA :
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="TWA Subject"
                type="text"
                disabled={isEdit}
                value={twaSubject}
                variant="outlined"
                onChange={(e) => { setTwaSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="TWA Body"
                type="text"
                disabled={isEdit}
                value={twaBody}
                variant="outlined"
                onChange={(e) => { setTwaBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} style={{
            padding:'2px',
            marginTop: '2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              Warning :
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Warning Subject"
                type="text"
                disabled={isEdit}
                value={warningSubject}
                variant="outlined"
                onChange={(e) => { warningSubject, setWarningSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Warning Body"
                type="text"
                disabled={isEdit}
                value={warningBody}
                variant="outlined"
                onChange={(e) => { setWarningBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} style={{
            padding:'2px',
            marginTop: '2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              Critical :
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Critical Subject"
                type="text"
                disabled={isEdit}
                value={criticalSubject}
                variant="outlined"
                onChange={(e) => { setCriticalSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Critical Body"
                type="text"
                disabled={isEdit}
                value={criticalBody}
                variant="outlined"
                onChange={(e) => { setCriticalBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} style={{
            padding:'2px',
            marginTop: '2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              Out of Range:
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Out of Range Subject"
                type="text"
                disabled={isEdit}
                value={outOfRangeSubject}
                variant="outlined"
                onChange={(e) => { setOutOfRangeSubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Out of Range Body"
                type="text"
                disabled={isEdit}
                value={outOfRangeBody}
                variant="outlined"
                onChange={(e) => { setOutOfRangeBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
          <Grid container spacing={1} style={{
            padding:'2px',
            marginTop: '2px'
          }}>
            <Grid item xs={12} sm={2} md={2} lg={2} xl={2}
              style={{
                alignSelf: 'center'
              }}
            >
              Periodicity :
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Periodicity Subject"
                type="text"
                disabled={isEdit}
                value={periodicitySubject}
                variant="outlined"
                onChange={(e) => { setPeriodicitySubject(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
            <Grid item xs={12} sm={5} md={5} lg={5} xl={5}>
              <TextField
                fullWidth
                // sx={{ mb: 1 }}
                label="Periodicity Body"
                type="text"
                disabled={isEdit}
                value={periodicityBody}
                variant="outlined"
                onChange={(e) => { setPeriodicityBody(e.target.value); }}
                autoComplete="off"
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </Grid>
          </Grid>
        </Grid>
        <Grid container style={{
          display: 'block',
            // float: 'right'
          }}>
          <Grid item style={{
            float: 'right'
          }}>
            <Button
              type="submit"
              disabled={isEdit}
            >
              Update
            </Button>
            <Button
              onClick={onCancel}
            >
              Cancel
            </Button>
          </Grid>
        </Grid>
      </form> */}
    </>}
    <NotificationBar
      handleClose={handleClose}
      notificationContent={openNotification.message}
      openNotification={openNotification.status}
      type={openNotification.type}
    />
  </Grid>
  )
}

export default EmailConfig