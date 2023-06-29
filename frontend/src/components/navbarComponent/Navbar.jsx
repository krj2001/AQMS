// import './navbar.scss';
import {
  ChatBubbleOutlineOutlined,
  AccountCircle,
  ErrorOutlineOutlined,
  WarningAmber,
  Fullscreen,
  FullscreenExit,
  PriorityHigh,
  InfoOutlined,
  Info,
  BrowserUpdatedRounded,
  CalendarMonthRounded,
} from '@mui/icons-material';
// import { GiLifeTap } from 'react-icons/gi';
// import NotificationsActiveIcon from '@mui/icons-material/NotificationsActive';
import NotificationsActiveOutlinedIcon from '@mui/icons-material/NotificationsActiveOutlined';
import { RiUserShared2Line } from 'react-icons/ri';
import PersonIcon from '@mui/icons-material/Person';
import { IoMdNotificationsOutline } from 'react-icons/io'
import PermDeviceInformationIcon from '@mui/icons-material/PermDeviceInformation';
import PrivacyTipOutlinedIcon from '@mui/icons-material/PrivacyTipOutlined';
// import { BiMessageSquareError } from 'react-icons/bi'
import { useEffect, useState } from 'react';
import {
  IconButton, Toolbar, Menu, MenuItem, ListSubheader, ListItemAvatar, ListItemText, ListItem, Typography, Tooltip, Zoom, Chip, createTheme, ThemeProvider,
} from '@mui/material';
import { useNavigate } from 'react-router-dom';
import MenuIcon from '@mui/icons-material/Menu';
import { LogoutService } from '../../services/LoginPageService';
import NotificationBar from '../notification/ServiceNotificationBar';

import ApplicationStore from '../../utils/localStorageUtil';
import LogIntervalSetting from './LogIntervalSettingComponent';

// import { DarkModeContext } from "../../context/darkModeContext";
// import { useContext } from "react";
/* eslint-disable no-nested-ternary */

function Navbar(props) {
  // const { dispatch } = useContext(DarkModeContext);
  const navigate = useNavigate();
  const { userDetails, intervalDetails, applicationDetails } = ApplicationStore().getStorage('userDetails');
  const userRole = userDetails?.userRole;
  const [userDisplayName, setUserDisplayName] = useState('');
  const [customerDisplayName, setCustomerDisplayName] = useState('Company Name Here...');
  const [currentVersion, setCurrentVersion] = useState('');
  const [releaseDate, setReleaseDate] = useState('');
  const [open, setOpen] = useState(false);
  const [anchorEl, setAnchorEl] = useState(null);
  const [infoAnchorEl, setInfoAnchorEl] = useState(null);
  const [openNotification, setNotification] = useState({
    status: false,
    type: '',
    message: '',
  });

  const [alertList, setAlertList] = useState(props.notificationList || []);
  const [uniqueAlert, setUniqueAlert] = useState([]);

  useEffect(() => {
    if (userDetails.userName) {
      setUserDisplayName(userDetails.userName);
      setCustomerDisplayName(userDetails.companyName);
      setCurrentVersion(applicationDetails?.applicationVersion || '');
      setReleaseDate(() => {
        if (applicationDetails?.releaseDate) {
          var date = applicationDetails.releaseDate.split("T");
          return date[0];
        }
        return ''
      }
      );
    }
    setInterval(() => {
    }, 1000);
  }, []);

  useEffect(() => {
    setAlertList(props.notificationList || []);

    // Set unique id for alerts
    const currentTime = new Date();

    var timeStamp = currentTime.toLocaleDateString('es-CL') + '_' + currentTime.toLocaleTimeString('en', {
      hour: 'numeric', hour12: true, minute: 'numeric', second: 'numeric',
    });

    let uniqueIdList = props.notificationList?.map((data, index) => {
      return { id: data.id, uniqueAlertId: data.id + '_' + userDisplayName + '_' + timeStamp }
    });

    // console.log(uniqueIdList || []);
    // console.log(timeStamp);

    // setUniqueAlert(uniqueIdList);
  }, [props.notificationList]);

  const handleMenu = (event) => {
    setAnchorEl(event.currentTarget);
  };

  const handleInfoMenu = (event) => {
    setInfoAnchorEl(event.currentTarget);
  };

  const handleInfoClose = () => {
    setInfoAnchorEl(null);
  };

  const handleNotificationMenu = (event) => {
    props.setAnchorElNotification(event.currentTarget);
    // assigning unique Id for top most alert list
    let uniqueAlert = [];
    uniqueAlert.push(alertList[0]);
    console.log(uniqueAlert[0]);
  };

  const handleClose = () => {
    setAnchorEl(null);
    props.setAnchorElNotification(null);
    setTimeout(() => {
      setAlertList([]);
    }, 500);
  };

  const logout = () => {
    LogoutService(logoutSuccessCallback, logoutErrorCallBack);
    handleClose();
  };

  const logoutSuccessCallback = (dataObject) => {
    setNotification({
      status: true,
      type: 'success',
      message: dataObject.message,
    });

    setTimeout(() => {
      handleNotificationClose();
      // ApplicationStore().setStorage('userDetails', '');
      // ApplicationStore().setStorage('siteDetails', '');
      // ApplicationStore().setStorage('alertDetails', '');
      // ApplicationStore().setStorage('notificationDetails', '');
      // ApplicationStore().setStorage('navigateDashboard', '');
      ApplicationStore().clearStorage();
      navigate('/login');
    }, 2000);
  };

  const logoutErrorCallBack = () => { };

  const handleNotificationClose = () => {
    setNotification({
      status: false,
      type: '',
      message: '',
    });
  };

  const alertIcon = (alertType) => {
    console.log("alertIcon", alertType)
    switch (alertType) {
      case 'Critical': return (<ErrorOutlineOutlined sx={{ color: 'red', fontSize: 30 }} />);
      case 'Warning': return (<ErrorOutlineOutlined style={{ color: 'yellow', fontSize: 30 }} />);
      case 'outOfRange': return (<ErrorOutlineOutlined sx={{ color: '#ba68c8', fontSize: 30 }} />);
      case 'Stel': return (<ErrorOutlineOutlined sx={{ color: 'red', fontSize: 30 }} />);
      case 'TWA': return (<ErrorOutlineOutlined sx={{ color: 'yellow', fontSize: 30 }} />);
      case 'deviceDisconnected': return (<ErrorOutlineOutlined sx={{ color: 'gray', fontSize: 30 }} />);
      default: return (<ErrorOutlineOutlined sx={{ color: 'yellow', fontSize: 30 }} />)
    }
  }

  const theme = createTheme({
    components: {
      // Name of the component
      MuiPopover: {
        styleOverrides: {
          // Name of the slot
          root: {
            // Some CSS
            fontSize: '1rem',
          },
          paper: {
            // border: '2px solid blue',
            borderRadius: '5px',
            boxShadow: `0 2.8px 2.2px rgba(0, 0, 0, 0.034),
            0 6.7px 5.3px rgba(0, 0, 0, 0.048),
            0 12.5px 10px rgba(0, 0, 0, 0.06),
            0 22.3px 17.9px rgba(0, 0, 0, 0.072),
            0 41.8px 33.4px rgba(0, 0, 0, 0.086),
            0 100px 80px rgba(0, 0, 0, 0.12)`
          },
          list: {
            padding: '0px'
          }
        },
      },
      MuiList: {
        styleOverrides: {
          root: {
            padding: '0px'
          }
        }
      }
    },
  });

  return (
    <div className="navbar h-24 w-fll  mb-0  py-4 flex text-[#252525]">
      <Toolbar>
        <IconButton
          color="inherit"
          aria-label="open drawer"
          edge="start"
          onClick={props.handleDrawerToggle}
          sx={{ mr: 0, display: { sm: 'flex' } }}
        >
          <MenuIcon sx={{ display: { xs: 'block', sm: 'block', md: 'none' } }} />
          {props.mobileMenu
            ? <Fullscreen sx={{ display: { xs: 'none', sm: 'none', md: 'block' } }} />
            : <FullscreenExit sx={{ display: { xs: 'none', sm: 'none', md: 'block' } }} />}
        </IconButton>
      </Toolbar>
      <div className="wrapper w-full m-0 flex items-center justify-end">
        <div
          className="wrapper text-center float-left w-full"
          style={{
            display: props.mobileMenu ? 'block' : 'none',
          }}
        >
          <div className='flex items-center'>
            {/* <GiLifeTap className='rounded-xl py-2 mr-1 ml-0 bg-white text-[42px]' /> */}
            <Typography variant="h4" fontSize={"24px"} color={"black"} width={"320px"} borderRadius={"16px"} padding={"15px 0px"} fontFamily={"customfont"} fontWeight={'700'} letterSpacing={'1px'} textAlign={'left'}>
              {props.mobileMenu ? customerDisplayName : ''}
            </Typography>
          </div>
        </div>
        <div className="items flex items-cente w-full justify-end">
          {userDetails.userRole !== 'superAdmin' &&
            <>
              <div className="item text-xs text-black mt-3 px-0 font-semibold font-[inherit] mr-4 min-[320px]:mr-0 min-[768px]:mr-5">
              {userDisplayName}
              {/* <p className='text-xs font-thin'>Type</p> */}
            </div>
              <Tooltip title="Notifications" placement="bottom" TransitionComponent={Zoom} arrow>
                <div className="notification item flex items-center mr-5 relative ">
                  <IoMdNotificationsOutline className=' icon rounded-xl py-2  mr-2 bg-white text-[44px] cursor-pointer' onClick={handleNotificationMenu} style={{boxShadow: 'rgba(99, 99, 99, 0.2) 0px 2px 8px 0px'}} />
                  <div className="counter w-6/12 h-3 rounded-full text-white flex items-center justify-center text-xs font-bold absolute bottom-8 left-7 right-0 p-2 text-[10px] bg-[rgb(236,53,53)]">
                    {props.notificationCount}
                  </div>
                </div>
              </Tooltip>
              <Menu
                id="menu-appbar1"
                anchorEl={props.anchorElNotification}
                anchorOrigin={{
                  vertical: 'top',
                  // horizontal: 'left',
                }}
                keepMounted
                transformOrigin={{
                  vertical: 'top',
                  // horizontal: 'left',
                }}
                open={Boolean(props.anchorElNotification)}
                onClose={handleClose}
                sx={{ height: 'auto', maxHeight: '60vh', width: '100%' }}
                style={{ overflow: 'none', marginTop: 36 }}
                PaperProps={{
                  elevation: 0,
                  sx: {
                    overflow: 'visible',
                    filter: 'drop-shadow(0px 2px 8px rgba(0,0,0,0.32))',
                    mt: 1.5,
                    '& .MuiAvatar-root': {
                      width: 32,
                      height: 32,
                      ml: -0.5,
                      mr: 1,
                    },
                    '&:before': {
                      content: '""',
                      display: 'block',
                      position: 'absolute',
                      top: 0,
                      // right: 145,
                      left: 20,
                      width: 10,
                      height: 10,
                      bgcolor: 'background.paper',
                      transform: 'translateY(-50%) rotate(45deg)',
                      zIndex: 0,
                    },
                  },
                }}
              >
                <div className='overflow-auto max-h-[50vh]'>
                  {/* style={{ overflow: 'auto', maxHeight: '50vh' }} */}
                  {alertList?.length !== 0
                    ? alertList?.map(({
                      id, sensorTag, a_date, a_time, msg, alertType, deviceName, labDepName,
                      floorName, buildingName, facilityName, branchName, stateName,
                    }) => (
                      <div key={id}>
                        <ListSubheader
                          sx={{ bgcolor: 'background.paper', height: '20px' }}
                          className='bg-[#e6f8ff] leading-[inherit]'
                        // style={{ backgroundColor: '#e6f8ff', paddingTop: '0px', lineHeight: 'inherit' }}
                        >
                          {a_date}
                          <div className='float-right h-5'  >
                            {/* style={{ float: 'right', height: '20px' }} */}
                            {a_time}
                          </div>
                        </ListSubheader>
                        <ListItem
                          button
                          onClick={handleClose}
                          className='min-h-80 px-0 max-w-lg'
                        // style={{
                        //   maxWidth: 500, minWidth: '300px', paddingTop: '0px', paddingBottom: '0px',
                        // }}
                        >
                          <ListItemAvatar>
                            {/* {alertType === 'Critical' ? <ErrorOutlineOutlined sx={{ color: 'red', fontSize: 30 }} /> :
                          alertType === 'Warning' ?  <PriorityHigh style={{ color: 'yellow', fontSize: 30 }}/> :
                          <WarningAmber sx={{ color: '#ba68c8', fontSize: 30 }} />} */}
                            {alertIcon(alertType)}
                          </ListItemAvatar>
                          <ListItemText
                            primary={<div>
                              <div><span className='font-bold'>
                                {/* style={{ fontWeight: 'bold' }} */}
                                {
                                  sensorTag === deviceName ?
                                    'Device Name:' :
                                    'Sensor Name:'
                                }
                              </span>
                                <span >{sensorTag}</span></div>
                              {/* style={{ fontWeight: 'none' }} */}
                            </div>}
                            secondary={<div>
                              <div>Message : {msg}</div>
                              {
                                sensorTag !== deviceName &&
                                <div>Device Name : {deviceName} </div>
                              }

                              <div>Lab : {labDepName} </div>
                              <div>Floor : {floorName} </div>
                              <div>Building : {buildingName} </div>
                              <div>Facility : {facilityName} </div>
                              <div>Branch : {branchName} </div>
                              <div>State : {stateName} </div>
                            </div>} />
                        </ListItem>
                      </div>
                    ))
                    : (
                      <div>
                        <ListItem button onClick={handleClose} className='max-w-lg min-w-xs text-center' >
                          {/* style={{ maxWidth: 500, minWidth: '300px', textAlign: 'center' }} */}
                          <ListItemText primary="" secondary="No Notifications found" />
                        </ListItem>
                      </div>
                    )}
                </div>
              </Menu>
            </>}
          {/* <IconButton
            size="small"
            aria-label="account of current user"
            // aria-controls="menu-appbar"
            // aria-haspopup="true"
            onClick={handleMenu}
            color="inherit"
          > */}

          <div aria-label="delete" onClick={handleInfoMenu} className='mr-7 min-[320px]:mr-4 min-[768px]:mr-5 '>
            <PrivacyTipOutlinedIcon className=' text-black icon rounded-xl py-2  ml-0 bg-white cursor-pointer' sx={{fontSize:"42px"}} style={{boxShadow: 'rgba(99, 99, 99, 0.2) 0px 2px 8px 0px'}} />
          </div>
          {/* ---------------------------------------------------------------------- */}
          <div className='flex items-center justify-start gap-1 sm:gap-5'>
            
            {/* </IconButton> */}
            <Menu
              id="menu-appbar"
              anchorEl={anchorEl}
              anchorOrigin={{
                vertical: 'bottom',
                // horizontal: 'right',
              }}
              keepMounted
              transformOrigin={{
                vertical: 'top',
                // horizontal: 'right',
              }}

              open={Boolean(anchorEl)}
              onClose={handleClose}
            >
              {(userRole === 'systemSpecialist' || userRole === 'superAdmin' || userRole === 'Admin' || userRole === 'Manager')
                && (
                  <MenuItem onClick={() => {
                    handleClose();
                    setOpen((oldValue) => !oldValue);
                  }}
                  >
                    Settings
                  </MenuItem>
                )}
              <MenuItem onClick={logout}>Logout</MenuItem>
            </Menu>

            <div className="item" style={{ marginRight: '10px' }} onClick={handleMenu}>
              <RiUserShared2Line className=' icon rounded-xl py-2   bg-white text-[42px] cursor-pointer' sx={{fontSize:"42px"}} style={{boxShadow: 'rgba(99, 99, 99, 0.2) 0px 2px 8px 0px'}} />
            </div>
          </div>
          {/* <IconButton aria-label="delete" onClick={handleInfoMenu} > */}
          {/* <div aria-label="delete" onClick={handleInfoMenu}>
            <BiMessageSquareError  className= ' text-black icon rounded-xl py-2 mr-5 ml-0 bg-white' style={{fontSize:'40px',
                      cursor: 'pointer',}} />
          </div> */}

          {/* </IconButton> */}
          <ThemeProvider theme={theme} >
            <Menu
              id="menu-appbar"
              anchorEl={infoAnchorEl}
              anchorOrigin={{
                vertical: 'bottom',
                horizontal: 'left',
              }}
              keepMounted
              transformOrigin={{
                vertical: 'top',
                horizontal: 'left',
              }}
              open={Boolean(infoAnchorEl)}
              onClose={handleInfoClose}
            >
              <div className='p-2.5'>
                {/* style={{
                padding : '10px '
              }} */}
                <div className='flex w-full gap-1' >
                  {/* style={{
                  display: 'flex',
                  width: '100%',
                  gap: '5px'
                }} */}
                  <BrowserUpdatedRounded />
                  <div className='flex flex-row w-[inherit] justify-between flex-nowrap' >
                    {/* style={{
                    display: 'flex',
                    flexDirection: 'row',
                    width: 'inherit',
                    justifyContent: 'space-between',
                    flexWrap: 'nowrap'
                  }} */}
                    <span className='font-bold'>
                      {/* style={{
                      fontWeight: 600
                    }} */}
                      App version :
                    </span>
                    <span>
                      {currentVersion}
                    </span>
                  </div>
                </div>
                <div className='flex w-full gap-1'>
                  <CalendarMonthRounded />
                  <div className='flex flex-row w-[inherit] justify-between flex-nowrap'>
                    <span className='font-bold'>
                      {/* style={{
                      fontWeight: 600
                    }} */}
                      Release Date :
                    </span>
                    <span>
                      {releaseDate}
                    </span>
                  </div>
                </div>
              </div>
            </Menu>
          </ThemeProvider>
        </div>
      </div>

      <LogIntervalSetting
        open={open}
        setOpen={setOpen}
        setNotification={setNotification}
        handleClose={handleClose}
        intervalDetails={intervalDetails}
        userRole={userRole}
      />
      <NotificationBar
        handleClose={handleNotificationClose}
        notificationContent={openNotification.message}
        openNotification={openNotification.status}
        type={openNotification.type}
      />
    </div>
  );
}

export default Navbar;