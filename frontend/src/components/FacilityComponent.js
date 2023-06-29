import React, { useEffect, useState } from 'react';
import Grid from '@mui/material/Grid';
import { Container } from '@mui/material';
import { useLocation } from 'react-router-dom';
import { FacilityListResults } from './siteDetails/facility/facilityList';
import MapsMultiplePoints from './maps/mapsMultiplePoints';

function Facility() {
  const routeStateObject = useLocation();
  const { centerCoordination } = routeStateObject.state;
  const [locationCoordinationList, setLocationCoordinationList] = useState([]);
  const [centerLat, setCenterLat] = useState(23.500);
  const [centerLng, setCenterLng] = useState(80.500);
  useEffect(() => {
    const coordinates = centerCoordination ? centerCoordination.replaceAll('"', '').split(',') : [];
    setCenterLat(parseFloat(coordinates[0]) || 23.500);
    setCenterLng(parseFloat(coordinates[1]) || 80.500);
  }, [locationCoordinationList]);
  return (
    <Container maxWidth={false} sx={{
      marginTop: 0, height: '94vh', width: '100%', paddingLeft: '24px', paddingRight: '12px'
    }}>
      <Grid container style={{ height: 'auto', width: '100%' }}>
        <Grid sx={{ mt: 1,mb:3, background: 'white', borderRadius: '12px', boxShadow: 'rgba(100, 100, 111, 0.2) 0px 7px 29px 0px',  }} xs={12} sm={12} md={12} lg={12} xl={12}
          // style={{
          //   height: '46vh',
          //   marginTop: '0px',
          //   minHeight: '300px',
          //   marginBottom: '20px'
          // }}
          className='h-[53vh] sm:h-[43vh] xl:h-[43vh] p-0 sm:p-4'
          >
          <FacilityListResults
            locationCoordinationList={locationCoordinationList}
            setLocationCoordinationList={setLocationCoordinationList}
            centerLat={centerLat}
            centerLng={centerLng}
          />
        </Grid>
        <Grid sx={{ mt: 1 }} xs={12} sm={12} md={12} lg={12} xl={12}
          className={' pb-10 sm:pb-2 h-[40vh]'}
          style={{
            // height: '47vh',
            borderRadius: '12px'
          }}
        >
          {locationCoordinationList.length !== 0
            ? (
              <MapsMultiplePoints
                width="100%"
                height="100%"
                markers={locationCoordinationList}
                zoom={10}
                center={{ lat: locationCoordinationList[0].position.lat, lng: locationCoordinationList[0].position.lng }}
              />
            )
            : ''}
        </Grid>
      </Grid>
    </Container>
  );
}

export default Facility;
