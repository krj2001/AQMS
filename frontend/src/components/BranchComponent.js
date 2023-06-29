import React, { useEffect, useState } from 'react';
import Grid from '@mui/material/Grid';
import { Container } from '@mui/material';
import { useParams, useLocation } from 'react-router-dom';
import { BranchListResults } from './siteDetails/branch/branchList';
import MapsMultiplePoints from './maps/mapsMultiplePoints';

function Branch() {
  const routeStateObject = useLocation();
  const { centerCoordination } = routeStateObject.state;
  const [locationCoordinationList, setLocationCoordinationList] = useState([]);
  const [centerLat, setCenterLat] = useState(21.785);
  const [centerLng, setCenterLng] = useState(72.91655655);
  const { locationId } = useParams();
  useEffect(() => {
    const coordinates = centerCoordination ? centerCoordination.replaceAll('"', '').split(',') : [];
    setCenterLat(parseFloat(coordinates[0]) || '');
    setCenterLng(parseFloat(coordinates[1]) || '');
  }, [locationCoordinationList]);
  return (
    <Container
      maxWidth={false} sx={{
        marginTop: 0, height: '94vh', width: '100%',
        // paddingLeft: '24px',
        // paddingRight: '12px',
        // marginTop: '16px'
      }}>
      <Grid container style={{  height: 'auto', width: '100%', }}>
        <Grid item sx={{ mt: 1,mb:3, background: 'white', borderRadius: '12px', boxShadow: 'rgba(100, 100, 111, 0.2) 0px 7px 29px 0px', padding: '16px' }} xs={12} sm={12} md={12} lg={12} xl={12}
          // style={{
          //   height: '46vh',
          //   marginTop: '0px',
          //   minHeight: '300px',
          //   marginBottom: '20px'
          // }}
          className='h-[53vh] sm:h-[48vh] xl:h-[43vh]'
          >
          <BranchListResults
            locationId={locationId}
            locationCoordinationList={locationCoordinationList}
            setLocationCoordinationList={setLocationCoordinationList}
            centerLat={centerLat}
            centerLng={centerLng}
          />
        </Grid>
        <Grid item sx={{ mt: 1 }} xs={12} sm={12} md={12} lg={12} xl={12}
          className={' pb-10 sm:pb-2 h-[40vh]'}
          style={{
           
            borderRadius: '12px'
          }}
        >
          {locationCoordinationList.length !== 0
            ? (
              <MapsMultiplePoints
                width="100%"
                height="100%"
                boxShadow= 'rgba(100, 100, 111, 0.2) 0px 7px 29px 0px'
                markers={locationCoordinationList}
                zoom={6}
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

export default Branch;
