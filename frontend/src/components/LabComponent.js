import React from 'react';
import Box from '@mui/material/Box';
import Grid from '@mui/material/Grid';
import { Container } from '@mui/material';
import { useLocation } from 'react-router-dom';
import { LabListResults } from './siteDetails/lab/labList';
import ApplicationStore from '../utils/localStorageUtil';

function Lab() {
  const routeStateObject = useLocation();
  const { floorMap } = routeStateObject.state;
  const {locationDetails} = ApplicationStore().getStorage('userDetails');
  const {imageFloorURL} = locationDetails ;
 // const imgSrc = `http://localhost/backend/blog/public/${imageFloorURL || floorMap}`;
 const imgSrc = `${process.env.REACT_APP_API_ENDPOINT}blog/public/${imageFloorURL || floorMap}`; 
 return (
    <Container maxWidth={false} style={{ marginTop: '16px', height: 'auto', paddingLeft: '24px', paddingRight: '35px' }}>
    <Grid
      container
      spacing={2}
      columns={{
        xs: 12, sm: 12, md: 12, lg: 12, xl: 12,
      }}
      style={{
        height: 'auto',
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
          marginTop: '0px'
        }}
      >
        <LabListResults img={imgSrc} />
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
          // height: '70vh',
          // border: '1px solid black',
          paddingLeft: '0px',
          paddingTop: '0px',
          paddingBottom: '0px',
          marginTop: '2px'
        }}
      >
        {/* <div style={{
          width: `${99}%`, height: `${57}vh`, borderColor: 'black', border: `${2}px` + ' solid' + ' black',
        }}
        > */}
        <img
          src={imgSrc}
          style={{ width: `${99}%`, height: `${68}vh` }}
        />
        {/* </div> */}
      </Box>
    </Grid>
  </Container>
  );
}

export default Lab;
