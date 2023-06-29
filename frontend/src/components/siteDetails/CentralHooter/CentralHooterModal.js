import {
    Button, Dialog, DialogContent, DialogTitle, TextField, Grid, Checkbox, FormControlLabel, DialogActions, ButtonBase, Stack, Breadcrumbs, Typography,
  } from '@mui/material';
  import React, { useEffect, useState } from 'react';
  import NotificationBar from '../../notification/ServiceNotificationBar';
import { Link, useLocation } from 'react-router-dom';
import ApplicationStore from '../../../utils/localStorageUtil';

  
const CentralHooterModal = ({openCentralHooter, setOpenCentralHooter, setCentralButtonText,setColorValue,trueValue}) => {
    // const routeStateObject = useLocation();
    // const {
    //     location_id, branch_id, facility_id, building_id, buildingImg,
    //   } = routeStateObject.state;
    // const {
    //     locationLabel, branchLabel, facilityLabel, buildingLabel,
    //   } = ApplicationStore().getStorage('siteDetails');
    // const pathList = routeStateObject.pathname.split('/').filter((x) => x);
    // const pathname = pathList.map((data, index) => {
    // const path = data.replace(/%20/g, ' ');
    // return (path);
    // });
    
    const [openNotification, setNotification] = useState({
      status: false,
      type: 'error',
      message: '',
    });
  
    useEffect(() => {
        if(trueValue === false)
        {
            setCentralButtonText("REMOVE CENTRALIZED HOOTER");
            setColorValue('red');
            setOpenCentralHooter(false);

        }
    }, [trueValue]);
  
  
    const handleSubmit = () => {
        setCentralButtonText("REMOVE CENTRALIZED HOOTER");
        setColorValue('red');
        setOpenCentralHooter(false);
    };
  
    const handleSuccess = (dataObject) => {
      setNotification({
        status: true,
        type: 'success',
        message: dataObject.message,
      });
      setRefreshData((oldvalue) => !oldvalue);
      setTimeout(() => {
        handleClose();
        setOpen(false);
        
      }, 5000);
    };
  
    const handleException = (errorObject, errorMessage) => {
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
  
   const OnCancel=()=>{
    setOpenCentralHooter(false)
   }

  return (
    <Dialog
    sx={{ '& .MuiDialog-paper': { minWidth: '60%' } }}
    maxWidth="sm"
    open={openCentralHooter}
  >
    <DialogTitle>
     CENTRALIZED HOOTER
    </DialogTitle>
        <DialogContent>
            <form  onSubmit={handleSubmit}>
                <label style={{fontSize:'20px'}}><h1>Location Select</h1></label>
                {/* <Stack style={{
                        overflow: 'auto'
                    }}
                    width = {{
                        xs: '100vw',
                        sm: '100vw',
                        md: '54vw',
                        lg: '54vw',
                        xl: '56vw'
                    }}
                >
                <Breadcrumbs aria-label="breadcrumb" separator="›" style={{
                    // height: '2vh',
                    minHeight: '15px',
                    minWidth: 'max-content'  // enable for scroll bar
                    }}
                >
            {locationLabel ? (
                <Typography
                underline="hover"
                color="inherit"
                >
                Location
                </Typography>
            ) : (
                <Link underline="hover" color="inherit" to="/Location">
                Location
                </Link>
            )}
            { branchLabel
                ? (
                <Typography
                    underline="hover"
                    color="inherit"
                >
                    {pathname[1]}
                </Typography>
                )
                : (
                <Link
                    underline="hover"
                    color="inherit"
                    to={`/Location/${pathname[1]}`}
                    state={{
                    location_id,
                    }}
                >
                    {pathname[1]}
                </Link>
                )}
            {facilityLabel
                ? (
                <Typography
                    underline="hover"
                    color="inherit"
                >
                    {pathname[2]}
                </Typography>
                )
                : (
                <Link
                    underline="hover"
                    color="inherit"
                    to={`/Location/${pathname[1]}/${pathname[2]}`}
                    state={{
                    location_id,
                    branch_id,
                    }}
                >
                    {pathname[2]}
                </Link>
                )}
            {buildingLabel ? (
                <Typography
                    underline="hover"
                    color="inherit"
                >
                    {pathname[3]}
                </Typography>
                ) : (
                <Link
                    underline="hover"
                    color="inherit"
                    to={`/Location/${pathname[1]}/${pathname[2]}/${pathname[3]}`}
                    state={{
                    location_id,
                    branch_id,
                    facility_id,
                    }}
                >
                    {pathname[3]}
                </Link>
                )}
            <Typography
                underline="hover"
                color="inherit"
            >
                {pathname[4]}
            </Typography>
            </Breadcrumbs>
                </Stack> */}
                <Grid container>
                    <Grid item md={6}>
                        <FormControlLabel control={<Checkbox  />} label="Critical" />
                    </Grid>
                    <Grid item md={6}>
                        <FormControlLabel control={<Checkbox  />} label="TWA" />
                    </Grid>
                    <Grid item md={6}>
                        <FormControlLabel control={<Checkbox  />} label="Stel" />
                    </Grid>
                    <Grid item md={6}>
                        <FormControlLabel control={<Checkbox  />} label="Out Of Range" />
                    </Grid>
                    <Grid item md={6}>
                        <FormControlLabel control={<Checkbox  />} label="Waring" />
                    </Grid>
                    <Grid item md={6}>
                        <FormControlLabel control={<Checkbox  />} label="Disconnected" />
                    </Grid>

                </Grid>
                
            </form>
        </DialogContent>
        <DialogActions >
            <Grid container style={{display: 'flex',flexDirection: 'row-reverse'}}>
                <Grid>
                    <Button onClick={OnCancel}> Cancel</Button>
                </Grid>
                <Grid>
                     <Button onClick={handleSubmit}> Submit</Button>
                </Grid>
            </Grid>
        </DialogActions>

    <NotificationBar
      handleClose={handleClose}
      notificationContent={openNotification.message}
      openNotification={openNotification.status}
      type={openNotification.type}
    />
  </Dialog>
  )
}

export default CentralHooterModal