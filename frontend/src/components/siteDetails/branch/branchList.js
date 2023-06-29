import React, { useState, useEffect } from 'react';
import { DataGrid } from '@mui/x-data-grid';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/DeleteOutlined';
import { Breadcrumbs, Typography } from '@mui/material';
import { Link, useLocation } from 'react-router-dom';
import { BranchDeleteService, FetchBranchService } from '../../../services/LoginPageService';
import { BranchListToolbar } from './branch-list-toolbars';
import BranchModal from './BranchModalComponent';
import NotificationBar from '../../notification/ServiceNotificationBar';
import { useUserAccess } from '../../../context/UserAccessProvider';
import DeleteConfirmationDailog from '../../../utils/confirmDeletion';
import ApplicationStore from '../../../utils/localStorageUtil';
import { Card, CardContent, CardHeader, Divider } from '@mui/material';
import { MdLocationPin } from 'react-icons/md';

export function BranchListResults(props) {
  const branchColumns = [
    {
      field: 'branchName',
      headerName: 'Branch Name',
      width: 270,
      type: 'actions',
      renderCell: ((params) => {
        return (
          <>
            <div className='flex w-full justify-between'>
              <div>
              <MdLocationPin className='text-[18px] text-left' />
              </div>
              <div className='w-full'>
              <LinkTo selectedRow={params.row} />
              </div>
            </div>

          </>
        )
      }),
      getActions: (params) => [
        <LinkTo selectedRow={params.row} />,
      ],
    },
    {
      field: 'actions',
      type: 'actions',
      headerName: 'Actions',
      width: 150,
      cellClassName: 'actions',
      getActions: (params) => [
        <EditData selectedRow={params.row} />,
        <DeleteData selectedRow={params.row} />,
      ],
    },
  ];

  const [open, setOpen] = useState(false);
  const [deleteDailogOpen, setDeleteDailogOpen] = useState(false);
  const [deleteId, setDeleteId] = useState('');
  const [isAddButton, setIsAddButton] = useState(true);
  const [editData, setEditData] = useState([]);
  const [dataList, setDataList] = useState([]);
  const [isLoading, setGridLoading] = useState(true);
  const routeStateObject = useLocation();
  const { location_id, centerCoordination } = routeStateObject.state;
  const [refreshData, setRefreshData] = useState(false);
  const moduleAccess = useUserAccess()('location');
  const {locationLabel} = ApplicationStore().getStorage('siteDetails');
  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  useEffect(() => {
    FetchBranchService({
      location_id,
    }, handleSuccess, handleException);
  }, [refreshData]);

  const handleSuccess = (dataObject) => {
    setGridLoading(false);
    setDataList(dataObject.data);
    const newArray = dataObject.data ? dataObject.data.map((item) => {
      const coordinates = item.coordinates ? item.coordinates.replaceAll('"', '').split(',') : [];

      return {
        id: item.id,
        name: item.branchName,
        position: {
          lat: parseFloat(coordinates[0]),
          lng: parseFloat(coordinates[1]),
        },
      };
    })
      : [];
    props.setLocationCoordinationList(newArray);
  };
  /* eslint-disable-next-line */
  const handleException = (errorObject) => {
  };

  const deletehandleSuccess = (dataObject) => {
    setNotification({
      status: true,
      type: 'success',
      message: dataObject.message,
    });
    setRefreshData((oldvalue) => !oldvalue);
    setTimeout(() => {
      handleClose();
      setDeleteDailogOpen(false);
    }, 3000);
  };

  const deletehandleException = (errorObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
    setTimeout(() => {
      handleClose();
    }, 3000);
  };
  /* eslint-disable-next-line */
  function LinkTo(props) {
    return (
      <Link
        to={`${props.selectedRow.branchName}`}
        state={{
          location_id,
          branch_id: props.selectedRow.id,
          centerCoordination: props.selectedRow.coordinates
        }}
      >
        {props.selectedRow.branchName}
      </Link>
    );
  }
  /* eslint-disable-next-line */
  function EditData(props) {
    return (
      moduleAccess.edit
      && (
        <EditIcon
          onClick={() => {
            setIsAddButton(false);
            setEditData(props.selectedRow);
            setOpen(true);
          }}
          style={{ cursor: 'pointer' }}
        />
      ));
  }
  /* eslint-disable-next-line */
  function DeleteData(props) {
    return moduleAccess.delete && (
      <DeleteIcon
        onClick={() => {
          setDeleteId(props.selectedRow.id);
          setDeleteDailogOpen(true);
        }}
        style={{ cursor: 'pointer' }}
      />
    );
  }

  const handleClose = () => {
    setNotification({
      status: false,
      type: '',
      message: '',
    });
  };
  const pathname = routeStateObject.pathname.split('/').filter((x) => x);
  return (
    <div style={{ width: '100%', padding: '20px', paddingBottom: '0', marginTop: '0px' }}>
      <Breadcrumbs aria-label="breadcrumb" separator="›" style={{
        // height: '2vh',
        minHeight: '15px',
        fontFamily: 'customfont',
        fontWeight: '600',
        color: 'black',
        fontSize: '16px',
        letterSpacing: '1px'
      }}>
        {locationLabel ? (
          <Typography
            underline="hover"
            color="inherit"
            sx={{ fontFamily: 'customfont', fontWeight: '600' }}
          >
            Location
          </Typography>
        ) : (
          <Link underline="hover" color="inherit" to="/Location">
            Location
          </Link>
        )}
        <Typography
          underline="hover"
          color="inherit"
          sx={{ fontFamily: 'customfont', fontWeight: '600', fontSize: '16px', letterSpacing: '1px' }}
        >
          {pathname[1].replace(/%20/g, ' ')}
        </Typography>
      </Breadcrumbs>
      <BranchListToolbar
        setOpen={setOpen}
        setIsAddButton={setIsAddButton}
        setEditData={setEditData}
        userAccess={moduleAccess}
      />
      <DataGrid
        sx={{ border: 'none', fontFamily: 'customfont', color: 'black', marginTop: '0px' }}
        rows={dataList}
        columns={branchColumns}
        pageSize={3}
        loading={isLoading}
        rowsPerPageOptions={[3]}
        disableSelectionOnClick
        style={{
        //   // maxHeight: `${80}%`,
          height: '225px',
        //   minHeight: '180px'
        }}

      />
      <BranchModal
        isAddButton={isAddButton}
        editData={editData}
        open={open}
        setOpen={setOpen}
        locationId={location_id}
        setRefreshData={setRefreshData}
        locationCoordinationList={props.locationCoordinationList}
        centerCoord={{ lat: parseFloat(props.centerLat), lng: parseFloat(props.centerLng) }}
      />
      <NotificationBar
        handleClose={handleClose}
        notificationContent={openNotification.message}
        openNotification={openNotification.status}
        type={openNotification.type}
      />
      <DeleteConfirmationDailog
        open={deleteDailogOpen}
        setOpen={setDeleteDailogOpen}
        deleteId={deleteId}
        deleteService={BranchDeleteService}
        handleSuccess={deletehandleSuccess}
        handleException={deletehandleException}
      />
    </div>
  );
}
