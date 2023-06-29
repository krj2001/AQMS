import React, { useEffect, useState } from 'react';
import { Container } from '@mui/material';
import Grid from '@mui/material/Grid';
import { useLocation } from 'react-router-dom';
import { BuildingListResults } from './siteDetails/building/buildingList';
import MapsMultiplePoints from './maps/mapsMultiplePoints';

function Building() {
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
    <Container maxWidth={false} 
    sx={{ marginTop: 0, height: '94vh', width: '100%', paddingLeft: '24px', paddingRight: '12px'}}>
      <Grid container style={{ height: 'auto', width: '100%' }}>
        <Grid sx={{ mt: 1,mb:3, background: 'white', borderRadius: '12px', boxShadow: 'none', padding: '16px' }} xs={12} sm={12} md={12} lg={12} xl={12}
          // style={{
          //   height: 'auto',
          //   marginTop: '0px',
          //   minHeight: '310px',
          //   marginBottom: '20px'
          // }}
          className='h-[55vh] sm:h-[48vh] xl:h-[47vh] p-0 sm:p-4'
          >
          <BuildingListResults
            locationCoordinationList={locationCoordinationList}
            setLocationCoordinationList={setLocationCoordinationList}
            centerLat={centerLat}
            centerLng={centerLng}
          />
        </Grid>
        <Grid sx={{ mt: 1 }} xs={12} sm={12} md={12} lg={12} xl={12}
          className={' pb-10 sm:pb-2 h-[37vh]'}
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
                borderRadius="12px"
                markers={locationCoordinationList}
                zoom={17}
                center={{
                  lat: locationCoordinationList[0].position.lat
                    || centerLat,
                  lng: locationCoordinationList[0].position.lng
                    || centerLng,
                }}
              />
            )
            : ''}
        </Grid>
      </Grid>
    </Container>
  );
}

export default Building;
