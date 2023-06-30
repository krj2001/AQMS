import React from 'react';
import Box from '@mui/material/Box';
import Grid from '@mui/material/Grid';
import { Container } from '@mui/material';
import { useLocation } from 'react-router-dom';
import { FloorListResults } from './siteDetails/floor/floorList';
import ApplicationStore from '../utils/localStorageUtil';

function Floor() {
  const routeStateObject = useLocation();
  const { buildingImg } = routeStateObject.state;
  const {locationDetails} = ApplicationStore().getStorage('userDetails');
  const {imageBuildingURL} = locationDetails ;
  //const imgSrc = `http://localhost/backend/blog/public/${imageBuildingURL || buildingImg}`;
  const imgSrc = `${process.env.REACT_APP_API_ENDPOINT}blog/public/${imageBuildingURL || buildingImg}`;
  return (
    <Container maxWidth={false} style={{ marginTop: '16px', height: 'auto', paddingLeft: '24px', paddingRight: '35px' }}>
      <Grid
        container
        spacing={2}
        columns={{
          xs: 12, sm: 12, md: 12, lg: 12, xl: 12,
        }}
        className='h-auto lg:h-[60vh]'
        style={{
          // height: 'auto',
          marginLeft: '2px',
          marginTop: '0px',
          background: 'white',
          borderRadius: '12px',
          boxShadow: 'none',
          padding: '16px',
          marginBottom:'20px'
        }}
      >
        <Grid
          sx={{ mt: 1 }}
          item
          xs={12}
          sm={12}
          md={8}
          lg={8}
          xl={8}
          style={{
            height: '70vh',
            minHeight: '350px',
            paddingTop: '0px',
            paddingLeft: '0px',
            marginTop: '0px',
            width: '100%'
          }}
        >
          <FloorListResults img={imgSrc} />
        </Grid>

        <Box
          component={Grid}
          item
          xs={12}
          sm={12}
          md={4}
          lg={4}
          xl={4}
          display={{
            xs: 'block', sm: 'block', md: 'block', lg: 'block', lx: 'block',
          }}
          sx={{ mt: 2 }}
          style={{
            // height: 'auto',
            // border: '1px solid black',
            paddingLeft: '0px',
            paddingTop: '0px',
            paddingBottom: '0px',
            marginTop: '2px',
            // width: '100%'
          }}
        >
          <img
            className='object-cover w-full'
            src={imgSrc}
            style={{
              width: `${99}%`,
              // height: `${100}%`
              height:'50vh'
            }}
          />
        </Box>
      </Grid>
    </Container >
  );
}

export default Floor;
