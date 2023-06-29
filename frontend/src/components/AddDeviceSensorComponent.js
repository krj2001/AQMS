import React, { useState } from 'react';
import PropTypes from 'prop-types';
import Tabs from '@mui/material/Tabs';
import Tab from '@mui/material/Tab';
import Typography from '@mui/material/Typography';
import Box from '@mui/material/Box';
import { Breadcrumbs, Container, Stack } from '@mui/material';
import { Link, useLocation } from 'react-router-dom';
import AddDeviceListResults from './Device/subComponent/AddDeviceListResults';
import HorizontalLinearStepper from './Device/DeviceSensor';
import SensorAdd from './Device/SensorAdd';
import { useUserAccess } from '../context/UserAccessProvider';
import ApplicationStore from '../utils/localStorageUtil';

function TabPanel(props) {
  const {
    children, value, index, ...other
  } = props;
  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`simple-tabpanel-${index}`}
      aria-labelledby={`simple-tab-${index}`}
      {...other}
    >
      {value === index && (
        <Box sx={{ p: '10px' }}>
          <Typography>{children}</Typography>
        </Box>
      )}
    </div>
  );
}

TabPanel.propTypes = {
  children: PropTypes.node,
  index: PropTypes.number.isRequired,
  value: PropTypes.number.isRequired,
};

function a11yProps(index) {
  return {
    id: `simple-tab-${index}`,
    'aria-controls': `simple-tabpanel-${index}`,
  };
}

function DeviceListResults() {
  const {locationDetails} = ApplicationStore().getStorage('userDetails');
  const {imageLabURL} = locationDetails ;

  const [value, setValue] = React.useState(0);
  const handleChange = (event, newValue) => {
    setValue(newValue);
  };
  const routeStateObject = useLocation();
  const {
    location_id, branch_id, facility_id, building_id, floor_id, lab_id, buildingImg, floorMap,
  } = routeStateObject.state;
  const labMap = routeStateObject.state.lab_map;
  const moduleAccess = useUserAccess()('devicelocation');
  const {
    locationLabel, branchLabel, facilityLabel, buildingLabel, floorLabel, labLabel
  } = ApplicationStore().getStorage('siteDetails');

  const pathList = routeStateObject.pathname.split('/').filter((x) => x);
  const pathname = pathList.map((data, index) => {
    const path = data.replace(/%20/g, ' ');
    return (path);
  });
  return (
    <div className="" style={{ marginTop: 0, padding: 0, height: 'auto', }}>
      <Container maxWidth={false}
        style={{
          height: 'auto',
          width: '100%',
          paddingLeft: '24px',
          paddingRight: '12px',
          marginTop: '16px'

        }}>
        <Box sx={{
          height: '80vh', marginBottom:'30px', width: '100%', background: 'white', borderRadius: '12px', boxShadow: 'none', padding: '16px', overflowX:'hidden'
        }}
        >
          <Stack style={{
            // overflow: 'auto',
            
            padding:'15px 0px'
          }}
            // width={{
            //   xs: '100vw',
            //   sm: '100vw',
            //   md: '80vw',
            //   lg: '80vw',
            //   xl: '80vw'
            // }}
          >
            <Breadcrumbs aria-label="breadcrumb" separator="›" style={{
              // height: '2vh',
              minHeight: '15px',
              fontFamily: 'customfont',
              fontWeight: '600',
              color: 'black',
              fontSize: '16px',
              letterSpacing: '1px',
              padding: ' 0 20px',
              // marginBottom: '18px'
            }}>
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
              {branchLabel
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
              {floorLabel ? (
                <Typography
                  underline="hover"
                  color="inherit"
                >
                  {pathname[4]}
                </Typography>
              ) : (
                <Link
                  underline="hover"
                  color="inherit"
                  to={`/Location/${pathname[1]}/${pathname[2]}/${pathname[3]}/${pathname[4]}`}
                  state={{
                    location_id,
                    branch_id,
                    facility_id,
                    building_id,
                    buildingImg,
                  }}
                >
                  {pathname[4]}
                </Link>
              )}
              {labLabel ? (
                <Typography
                  underline="hover"
                  color="inherit"
                >
                  {pathname[5]}
                </Typography>
              ) : (
                <Link
                  underline="hover"
                  color="inherit"
                  to={`/Location/${pathname[1]}/${pathname[2]}/${pathname[3]}/${pathname[4]}/${pathname[5]}`}
                  state={{
                    location_id,
                    branch_id,
                    facility_id,
                    building_id,
                    floor_id,
                    buildingImg,
                    floorMap,
                  }}
                >
                  {pathname[5]}
                </Link>
              )}
              <Typography
                underline="hover"
                color="inherit"
                sx={{ fontFamily: 'customfont', fontWeight: '600' }}
              >
                {pathname[6]}
              </Typography>
            </Breadcrumbs>
          </Stack>
          <Box>
            <Tabs value={value} onChange={handleChange} aria-label="basic tabs example"
            variant='scrollable'
                visibleScrollbar={true}
                sx={{
                  // overflow: 'auto',
                  // width: 'auto',
                  marginLeft: '15px'
                }}>
              <Tab label="Devices" {...a11yProps(0)} sx={{ fontFamily: 'customfont', fontWeight: '600', letterSpacing: '1px' }} {...a11yProps(0)} />
              {moduleAccess.add && <Tab label="Add Devices" {...a11yProps(1)} sx={{ fontFamily: 'customfont', fontWeight: '600', letterSpacing: '1px' }} {...a11yProps(0)} />}
              {moduleAccess.add && <Tab label="Add Sensors" {...a11yProps(2)} sx={{ fontFamily: 'customfont', fontWeight: '600', letterSpacing: '1px' }} {...a11yProps(0)} />}
            </Tabs>
          </Box>
          <TabPanel value={value} index={0}>
            <AddDeviceListResults
              locationDetails={{
                location_id, branch_id, facility_id, building_id, floor_id, lab_id,
              }}
              labMap={imageLabURL || labMap}
            />
          </TabPanel>
          <TabPanel value={value} index={1}>
            <HorizontalLinearStepper
              locationDetails={{
                location_id, branch_id, facility_id, building_id, floor_id, lab_id,
              }}
              labMap={imageLabURL || labMap}
              setValue={setValue}
            />
          </TabPanel>
          <TabPanel value={value} index={2}>
            <SensorAdd locationDetails={{
              location_id, branch_id, facility_id, building_id, floor_id, lab_id,
            }}
            />
          </TabPanel>
        </Box>
      </Container>
    </div>
  );
}

export default DeviceListResults;
