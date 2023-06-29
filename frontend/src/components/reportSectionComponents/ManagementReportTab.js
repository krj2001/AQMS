import React, { useState, useEffect } from 'react';
import PropTypes from 'prop-types';
import {
    Tabs, Tab, Typography, Box, InputLabel, MenuItem, FormControl, Select, Stack, Grid, styled, Card, CardHeader, CardContent,
} from '@mui/material';
import AqiSitesReportForm from './AqiSitesReportForm';
import Alarm from './Alarm';
import ServerUtilization from './ServerUtilization';
import FirmwareVersion from './FirmwareVersion';
import BumpTest from './BumpTest';
import SensorStatus from './SensorStatus';
import DeviceLogs from './DeviceLogs';
import SoftwareVersion from './SoftwareVersion';

import {
    FetchLocationService,
    FetchBranchService,
    FetchFacilitiyService,
    BuildingFetchService,
    FloorfetchService,
    LabfetchService,
    DeviceFetchService,
    SearchDeviceDataService,
} from '../../services/LoginPageService';
import HardwareModelVersion from './HardwareModelVersion';
import ApplicationStore from '../../utils/localStorageUtil';
import CalibrationReport from './CalibrationReport';
import EVENT_LOG from './EVENT_LOG';

function TabPanel(props) {
    const { children, value, index } = props;
    return (
        <div
            role="tabpanel"
            hidden={value !== index}
            id={`simple-tabpanel-${index}`}
            aria-labelledby={`simple-tab-${index}`}
        >
            {value === index && (
                <Box sx={{ p: 3 }}>
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

export default function ManagementReportTab() {
    const [location_id, setLocation_id] = useState('');
    const [locationList, setLocationList] = useState([]);
    const [branch_id, setBranch_id] = useState('');
    const [branchList, setBranchList] = useState([]);
    const [facility_id, setFacility_id] = useState('');
    const [facilityList, setFacilityList] = useState([]);
    const [building_id, setBuilding_id] = useState('');
    const [buildingList, setBuildingList] = useState([]);
    const [floor_id, setFloor_id] = useState('');
    const [floorList, setFloorList] = useState([]);
    const [lab_id, setLab_id] = useState('');
    const [labList, setLabList] = useState([]);
    const [device_id, setDevice_id] = useState('');
    const [deviceList, setDeviceList] = useState([]);
    const { locationDetails } = ApplicationStore().getStorage('userDetails');
    useEffect(() => {
        loadLocation();
    }, []);

    useEffect(() => {
        SearchDeviceDataService({
            location_id, branch_id, facility_id, building_id, floor_id, lab_id,
        }, DeviceHandleSuccess, DeviceHandleException);
    }, [location_id, branch_id, facility_id, building_id, floor_id, lab_id ]);

    const loadLocation = () => {
        FetchLocationService(LocationHandleSuccess, LocationHandleException);
    };

    const LocationHandleSuccess = (dataObject) => {
        setLocationList(dataObject.data || []);
        if(locationDetails?.location_id){
            setLocation_id(locationDetails?.location_id);
            FetchBranchService({ location_id: locationDetails?.location_id }, BranchHandleSuccess, BranchHandleException);
        }
    };
    const LocationHandleException = () => { };

    const LocationChanged = (location_id) => {
        setLocation_id(location_id);
        setBranch_id('');
        setFacility_id('');
        setBuilding_id('');
        setFloor_id('');
        setLab_id('');
        setBranchList([]);
        setFacilityList([]);
        setBuildingList([]);
        setFloorList([]);
        setLabList([]);
        if(location_id){
            FetchBranchService({ location_id }, BranchHandleSuccess, BranchHandleException);
        }
    };

    const BranchHandleSuccess = (dataObject) => {
        setBranchList(dataObject.data || []);
        if(locationDetails?.branch_id){
            setBranch_id(locationDetails?.branch_id);
            FetchFacilitiyService({ 
                location_id: locationDetails?.location_id, 
                branch_id:locationDetails?.branch_id 
            }, FacilityHandleSuccess, FacilityHandleException
            );
        }
    };
    const BranchHandleException = () => { };

    const BranchChanged = (branch_id) => {
        setBranch_id(branch_id);
        setFacility_id('');
        setBuilding_id('');
        setFloor_id('');
        setLab_id('');
        setFacilityList([]);
        setBuildingList([]);
        setFloorList([]);
        setLabList([]);
        if(branch_id){
            FetchFacilitiyService({ location_id, branch_id }, FacilityHandleSuccess, FacilityHandleException);
        }
    };
    const FacilityHandleSuccess = (dataObject) => {
        setFacilityList(dataObject.data || []);
        if(locationDetails?.facility_id){
            setFacility_id(locationDetails?.facility_id);
            BuildingFetchService({ 
                location_id: locationDetails?.location_id, 
                branch_id: locationDetails?.branch_id, 
                facility_id: locationDetails?.facility_id,
            }, BuildingHandleSuccess, BuildingHandleException);
        }
    };

    const FacilityHandleException = () => { };

    const FacilityChanged = (facility_id) => {
        setFacility_id(facility_id);
        setBuilding_id('');
        setFloor_id('');
        setLab_id('');
        setBuildingList([]);
        setFloorList([]);
        setLabList([]);
        if(facility_id){
            BuildingFetchService({ location_id, branch_id, facility_id }, BuildingHandleSuccess, BuildingHandleException);
        }
    };

    const BuildingHandleSuccess = (dataObject) => {
        setBuildingList(dataObject.data || []);
        if(locationDetails?.building_id){
            setBuilding_id(locationDetails?.building_id);
            FloorfetchService({
                location_id: locationDetails?.location_id,
                branch_id: locationDetails?.branch_id,
                facility_id: locationDetails?.facility_id,
                building_id: locationDetails?.building_id,
            }, FloorHandleSuccess, FloorHandleException);
        }
    };

    const BuildingHandleException = () => { };

    const BuildingChanged = (building_id) => {
        setBuilding_id(building_id);
        setFloor_id('');
        setLab_id('');
        setFloorList([]);
        setLabList([]);
        if(building_id){
            FloorfetchService({
                location_id, branch_id, facility_id, building_id,
            }, FloorHandleSuccess, FloorHandleException);
        }
    };

    const FloorHandleSuccess = (dataObject) => {
        setFloorList(dataObject.data || []);
        if(locationDetails?.floor_id){
            setFloor_id(locationDetails?.floor_id);
            LabfetchService({
                location_id: locationDetails?.location_id,
                branch_id: locationDetails?.branch_id,
                facility_id: locationDetails?.facility_id,
                building_id: locationDetails?.building_id,
                floor_id: locationDetails?.floor_id,
            }, LabHandleSuccess, LabHandleException);
        }
    };
    const FloorHandleException = () => { };

    const FloorChanged = (floor_id) => {
        setFloor_id(floor_id);
        setLab_id('');
        setLabList([]);
        if(floor_id){
            LabfetchService({
                location_id, branch_id, facility_id, building_id, floor_id,
            }, LabHandleSuccess, LabHandleException);
        }
    };

    const LabHandleSuccess = (dataObject) => {
        setLabList(dataObject.data || []);
        if(locationDetails?.lab_id){
            setLab_id(locationDetails?.lab_id);
        }
    };

    const LabHandleException = () => { };

    const LabHandleChange = (lab_id) => {
        setLab_id(lab_id);
        // if(lab_id){
        //     SearchDeviceDataService({
        //         location_id, branch_id, facility_id, building_id, floor_id, lab_id,
        //     }, DeviceHandleSuccess, DeviceHandleException);
        // }
    };

    const DeviceHandleSuccess = (dataObject) => {
        setDeviceList(dataObject.data || []);
    };
    const DeviceHandleException = () => { };

    const [value, setValue] = React.useState(0);
    const handleChange = (event, newValue) => {
        setValue(newValue);
    };

    return (
        <>
            <div className={'px-5 pt-0 w-full '}>
                <Card sx={{ boxShadow: 'rgba(100, 100, 111, 0.2) 0px 7px 29px 0px', borderRadius: '12px' }}>
                    <CardHeader
                        title={
                            <Grid container spacing={1} xs={12} className={'mt-0.5 ml-0 pl-1.5'}>
                                <Grid item xs={12} sm={4} md={4} lg={3} xl={2}>
                                    <FormControl fullWidth size="small">
                                        <InputLabel sx={{ fontFamily: 'customfont' }}>Location</InputLabel>
                                        <Select
                                            value={location_id}
                                            label="Location"
                                            disabled={locationDetails?.location_id}
                                            onChange={(e) => {
                                                LocationChanged(e.target.value);
                                            }}
                                        >
                                            <MenuItem value="" key={0} sx={{ fontFamily: 'customfont' }}>
                                                <em className={'font-bold'}>All</em>
                                            </MenuItem>
                                            {locationList.map((data, index) => (
                                                <MenuItem value={data.id} key={index + 1} sx={{ fontFamily: 'customfont' }}>{data.stateName}</MenuItem>
                                            ))}
                                        </Select>
                                    </FormControl>
                                </Grid>
                                <Grid item xs={12} sm={4} md={4} lg={3} xl={2}>
                                    <FormControl fullWidth size="small" >
                                        <InputLabel sx={{ fontFamily: 'customfont' }}>Branch</InputLabel>
                                        <Select
                                            value={branch_id}
                                            label="Branch"
                                            disabled={locationDetails?.branch_id}
                                            onChange={(e) => {
                                                BranchChanged(e.target.value);
                                            }}
                                        >
                                            <MenuItem value="" key={0} sx={{ fontFamily: 'customfont' }}>
                                                <em className={'font-bold'}>All</em>
                                            </MenuItem>
                                            {branchList.map((data, index) => (
                                                <MenuItem value={data.id} key={index + 1} sx={{ fontFamily: 'customfont' }}>{data.branchName}</MenuItem>
                                            ))}
                                        </Select>
                                    </FormControl>
                                </Grid>
                                <Grid item xs={12} sm={4} md={4} lg={3} xl={2}>
                                    <FormControl fullWidth size="small" >
                                        <InputLabel sx={{ fontFamily: 'customfont' }}>Facility</InputLabel>
                                        <Select
                                            value={facility_id}
                                            label="Facility"
                                            disabled={locationDetails?.facility_id}
                                            onChange={(e) => {
                                                FacilityChanged(e.target.value);
                                            }}
                                        >
                                            <MenuItem value="" key={0}>
                                                <em className={'font-bold'} sx={{ fontFamily: 'customfont' }}>All</em>
                                            </MenuItem>
                                            {facilityList.map((data, index) => (
                                                <MenuItem value={data.id} key={index + 1} sx={{ fontFamily: 'customfont' }}>{data.facilityName}</MenuItem>
                                            ))}
                                        </Select>
                                    </FormControl>
                                </Grid>
                                <Grid item xs={12} sm={4} md={4} lg={3} xl={2}>
                                    <FormControl fullWidth size="small" >
                                        <InputLabel sx={{ fontFamily: 'customfont' }}>Building</InputLabel>
                                        <Select
                                            value={building_id}
                                            label="Building"
                                            disabled={locationDetails?.building_id}
                                            onChange={(e) => {
                                                BuildingChanged(e.target.value);
                                            }}
                                        >
                                            <MenuItem value="" key={0} sx={{ fontFamily: 'customfont' }}>
                                                <em className={'font-bold'}>All</em>
                                            </MenuItem>
                                            {buildingList.map((data, index) => (
                                                <MenuItem value={data.id} key={index + 1} sx={{ fontFamily: 'customfont' }}>{data.buildingName}</MenuItem>
                                            ))}
                                        </Select>
                                    </FormControl>
                                </Grid>
                                <Grid item xs={12} sm={4} md={4} lg={3} xl={2}>
                                    <FormControl fullWidth size="small" >
                                        <InputLabel sx={{ fontFamily: 'customfont' }}>Floor</InputLabel>
                                        <Select
                                            value={floor_id}
                                            label="Floor"
                                            disabled={locationDetails?.floor_id}
                                            onChange={(e) => {
                                                FloorChanged(e.target.value);
                                            }}
                                        >
                                            <MenuItem value="" key={0} sx={{ fontFamily: 'customfont' }}>
                                                <em className={'font-bold'}>All</em>
                                            </MenuItem>
                                            {floorList.map((data, index) => (
                                                <MenuItem value={data.id} key={index + 1} sx={{ fontFamily: 'customfont' }}>{data.floorName}</MenuItem>
                                            ))}
                                        </Select>
                                    </FormControl>
                                </Grid>
                                <Grid item xs={12} sm={4} md={4} lg={3} xl={2}>
                                    <FormControl fullWidth size="small" >
                                        <InputLabel sx={{ fontFamily: 'customfont' }}>Zone</InputLabel>
                                        <Select
                                            value={lab_id}
                                            label="Zone"
                                            onChange={(e) => {
                                                LabHandleChange(e.target.value);
                                            }}
                                        >
                                            <MenuItem value="" key={0} sx={{ fontFamily: 'customfont' }}>
                                                <em className={'font-bold'}>All</em>
                                            </MenuItem>
                                            {labList.map((data, index) => (
                                                <MenuItem value={data.id} key={index + 1} sx={{ fontFamily: 'customfont' }}>{data.labDepName}</MenuItem>
                                            ))}
                                        </Select>
                                    </FormControl>
                                </Grid>
                            </Grid>
                        }
                    />
                </Card>
                <Card className={'mt-5 mb-5'}
                    sx={{ boxShadow: 'rgba(100, 100, 111, 0.2) 0px 7px 29px 0px', borderRadius: '12px' }}>
                    <CardContent >
                        {/* <Grid container spacing={1} xs={12} item className={' overflow-hidden'}> */}
                            <Box width='100%' className='px-0 sm:px-0 '>
                                <Tabs value={value} onChange={handleChange} aria-label="basic tabs example"
                                    // scrollButtons="off"
                                    variant='scrollable'
                                    visibleScrollbar={true}
                                    allowScrollButtonsMobile
                                    sx={{
                                        display:'grid',
                                        padding: '0px 0',
                                        width: '100%',
                                    }}

                                >
                                    <Tab sx={{ fontFamily: 'customfont', fontWeight: '600', letterSpacing: '0.8px' }} label="Air Quality Index" {...a11yProps(0)} />
                                    <Tab sx={{ fontFamily: 'customfont', fontWeight: '600', letterSpacing: '0.8px' }} label="Sensor Status" {...a11yProps(1)} />
                                    <Tab sx={{ fontFamily: 'customfont', fontWeight: '600', letterSpacing: '0.8px' }} label="Alarms" {...a11yProps(2)} />
                                    <Tab sx={{ fontFamily: 'customfont', fontWeight: '600', letterSpacing: '0.8px' }} label="Limit Edit Logs" {...a11yProps(3)} />
                                    <Tab sx={{ fontFamily: 'customfont', fontWeight: '600', letterSpacing: '0.8px' }} label="Server Utilization" {...a11yProps(4)} />
                                    <Tab sx={{ fontFamily: 'customfont', fontWeight: '600', letterSpacing: '0.8px' }} label="Application version" {...a11yProps(5)} />
                                    <Tab sx={{ fontFamily: 'customfont', fontWeight: '600', letterSpacing: '0.8px' }} label="Firmware Version" {...a11yProps(6)} />
                                    <Tab sx={{ fontFamily: 'customfont', fontWeight: '600', letterSpacing: '0.8px' }} label="H/W Model No" {...a11yProps(7)} />
                                    <Tab sx={{ fontFamily: 'customfont', fontWeight: '600', letterSpacing: '0.8px' }} label="BumpTest" {...a11yProps(8)} />
                                    <Tab sx={{ fontFamily: 'customfont', fontWeight: '600', letterSpacing: '0.8px' }} label="Calibration" {...a11yProps(9)} /> 
                                    <Tab sx={{ fontFamily: 'customfont', fontWeight: '600', letterSpacing: '0.8px' }} label="EVENT LOG" {...a11yProps(10)} />        
                                </Tabs>
                            </Box>
                            <TabPanel value={value} index={0}>
                                <AqiSitesReportForm
                                    siteId={{ location_id, branch_id, facility_id, building_id, floor_id, lab_id }}
                                    deviceList={deviceList}
                                />
                            </TabPanel>
                            <TabPanel value={value} index={1}>
                                <SensorStatus
                                    deviceList={deviceList}
                                    siteId={{ location_id, branch_id, facility_id, building_id, floor_id, lab_id }}
                                />
                            </TabPanel>
                            <TabPanel value={value} index={2}>
                                <Alarm
                                    siteId={{ location_id, branch_id, facility_id, building_id, floor_id, lab_id }}
                                    deviceList={deviceList}
                                />
                            </TabPanel>
                            <TabPanel value={value} index={3}>
                                <DeviceLogs
                                    deviceList={deviceList}
                                    siteId={{ location_id, branch_id, facility_id, building_id, floor_id, lab_id }}
                                />
                            </TabPanel>
                            <TabPanel value={value} index={4}>
                                <ServerUtilization />
                            </TabPanel>
                            <TabPanel value={value} index={5}>
                                <SoftwareVersion />
                            </TabPanel>
                            <TabPanel value={value} index={6}>
                                <FirmwareVersion
                                    deviceList={deviceList}
                                    siteId={{ location_id, branch_id, facility_id, building_id, floor_id, lab_id }}
                                />
                            </TabPanel>
                            <TabPanel value={value} index={7}>
                                <HardwareModelVersion
                                    deviceList={deviceList}
                                    siteId={{ location_id, branch_id, facility_id, building_id, floor_id, lab_id }}
                                />
                            </TabPanel>
                            <TabPanel value={value} index={8}>
                                <BumpTest
                                    deviceList={deviceList}
                                    siteId={{ location_id, branch_id, facility_id, building_id, floor_id, lab_id }}
                                />
                            </TabPanel>
                            <TabPanel value={value} index={9}>
                            <CalibrationReport 
                                deviceList={deviceList} 
                                siteId ={{location_id, branch_id, facility_id, building_id, floor_id, lab_id}}
                            />
                        </TabPanel>
                        <TabPanel value={value} index={10}>
                            <EVENT_LOG 
                                deviceList={deviceList} 
                                siteId ={{location_id, branch_id, facility_id, building_id, floor_id, lab_id}}
                            />
                        </TabPanel>
                        {/* </Grid> */}
                    </CardContent>
                </Card>
            </div >
        </>
    );
}
