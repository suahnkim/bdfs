
CID=${1:-"CID0000000000327"}
CCID=${2:-"QmXxn8k7CppsFfi58XyR8vh2XDTkunKNz7JzwzVEuKX327"}
VERSION=${3:-'QmXxn8k7CppsFfi58XyR8vh2XDTkunKNz7JzwzVEuKX327'}
INFO=${4:-'eyJkYXRlIjoiMjAyMDA2MjMxNjU2NDkiLCJjYXQxIjoiQzAxIiwiY2F0MiI6IkMwMSIsImZ0eXBlIjoiVjAxIiwiaW5mbyI6eyJjb250ZW50c19pbmZvIjp7ImNvbnRlbnRzX2lkIjoiODIwIiwiY2F0ZTEiOiJDMDEiLCJjYXRlMiI6IiIsInVzZXJpZCI6IjliY2VjZDkwODVmYWU4ZmE3ODdhYzNmM2JkM2MyZjI1YTkwZTA2MTAiLCJuaWNrbmFtZSI6IiIsImx2IjoiMCIsInRpdGxlIjoibm9uIGRybSIsImNvbnRlbnRzIjoiXHVkMTRjXHVjMmE0XHVkMmI4IiwiaXNfZm9sZGVyIjoiTiIsImZvbGRlcl9uYW1lIjoiIiwic2l6ZSI6IjE3ODExOSIsImNhc2giOiIxMDAiLCJpc19hZHVsdCI6Ik4iLCJpc19yaWdodHMiOiJOIiwicHVyY2hhc2UiOiIwIiwiZG93biI6IjAiLCJldmEiOiIwIiwic2NvcmUiOiIwIiwiY29tbWVudCI6IjAiLCJjb2xvciI6IiIsImJvbGQiOiJOIiwidGh1bWIiOiIiLCJzdGF0ZSI6IjMiLCJ3ZGF0ZSI6IjE1OTI4OTg3NjMiLCJlZGF0ZSI6IjAiLCJzZGF0ZSI6IjAiLCJzb3J0IjoiMSIsImluaXRfa2V5IjoiY2EzOTkyZmYtYjVhOC00MDhhLTliZjEtMTEyYTJmYWQ4NmZkIiwibWFpbl9pbWciOiJFOlxcY29udGVudHNcXDIwMTkwOTA0XFwxNTY3NzMwNDk1LmpwZ3w5NjI0fDE4M3wyNzV8XC9kYXRhXC8yMDIwMDYyM1wvMTU5MzIxNjk2My5qcGciLCJzdWJfaW1nIjoiRTpcXGNvbnRlbnRzXFwyMDE5MDkwNFxcMTU2Nzc4Mjg3OC5qcGd8MTc4MTE5fDY0OHw5NjB8XC9kYXRhXC8yMDIwMDYyM1wvMTU5MzcwNTE3My5qcGcsRTpcXGNvbnRlbnRzXFwyMDE5MDkwNFxcMTU2NzgzNjM5Ni5qcGVnfDEyMTQzOTB8MTc2OHwxMjYyfFwvZGF0YVwvMjAyMDA2MjNcLzE1OTMwMTUwMzUuanBlZyIsImhhc2hfdGFncyI6Ilx1YjRkY1x1Yjc3Y1x1YjljOCIsImNpZCI6IkNJRDAwMDAwMDAwMDAwMjMiLCJjY2lkIjoiUW1YeG44azdDcHBzRmZpNThYeVI4dmgyWERUa3VuS056N0p6d3pWRXVLWGdTayIsImNjaWRfdmVyIjoiUW1QUVZmV2FrRVpwQ1d5dWVrdndnb3oybVd6Z1NVNlpUUVhzaXJxeWNKMnhKRiIsImRybSI6Ik4iLCJ3YXRlcm1hcmtpbmciOiJOIiwiaXBmc19qc29uX2RhdGEiOiJ7ICAgIFwicmVzdWx0XCI6IHsgICAgICAgIFwicmVzdWx0XCI6IFwiMFwiLCAgICAgICAgXCJyZXN1bHRfbWVzc2FnZVwiOiBcIk9LXCIsICAgICAgICBcImNjaWRcIjogXCJRbVh4bjhrN0NwcHNGZmk1OFh5Ujh2aDJYRFRrdW5LTno3Snp3elZFdUtYZ1NrXCIsICAgICAgICBcInZlcnNpb25cIjogXCJRbVBRVmZXYWtFWnBDV3l1ZWt2d2dvejJtV3pnU1U2WlRRWHNpcnF5Y0oyeEpGXCIsICAgICAgICBcImNodW5rX3NpemVcIjogMjYyMTQ0ICAgIH0sICAgIFwiZmlsZXNcIjogWyAgICAgICAgeyAgICAgICAgICAgIFwicGF0aFwiOiBcImJhc2ljTWV0YVwvMTU2NzczMDQ5NS5qcGdcIiwgICAgICAgICAgICBcImZpbGVfc2l6ZVwiOiA5NjI0LCAgICAgICAgICAgIFwiY2lkXCI6IFwiUW1OZ2F4WHRiUFJtc1hXVzdCOEZOeTVpV2dzM3hFSGo3Mkw3RWY4QnhxYzZ4YVwiICAgICAgICB9LCAgICAgICAgeyAgICAgICAgICAgIFwicGF0aFwiOiBcImJhc2ljTWV0YVwvMTU2Nzc4Mjg3OC5qcGdcIiwgICAgICAgICAgICBcImZpbGVfc2l6ZVwiOiAxNzgxMTksICAgICAgICAgICAgXCJjaWRcIjogXCJRbWQ1TmZ0d1VHajlZaGY1aFZXVGhpaUhkN3FpZnBFYVp1UVRVWERMcGdZdHFnXCIgICAgICAgIH0sICAgICAgICB7ICAgICAgICAgICAgXCJwYXRoXCI6IFwiYmFzaWNNZXRhXC8xNTY3ODM2Mzk2LmpwZWdcIiwgICAgICAgICAgICBcImZpbGVfc2l6ZVwiOiAxMjE0MzkwLCAgICAgICAgICAgIFwiY2lkXCI6IFwiUW1mVlFmalYyNGdGcnN6WHBzN2lBclIxWHpBSHhzNVNiREdjem5kSDZkZjlYOFwiICAgICAgICB9LCAgICAgICAgeyAgICAgICAgICAgIFwicGF0aFwiOiBcImJhc2ljTWV0YVwvYmFzaWNNZXRhMC5qc29uXCIsICAgICAgICAgICAgXCJmaWxlX3NpemVcIjogMzk1OSwgICAgICAgICAgICBcImNpZFwiOiBcIlFtZWROREJaZjQ2OXZvbzM4NldBQVBNN3pTQkZnMjFGekM4czU0TFl6QWtyYkxcIiAgICAgICAgfSwgICAgICAgIHsgICAgICAgICAgICBcInBhdGhcIjogXCJjb250ZW50c1wvMTU2Nzc3OTQzOS5qcGdcIiwgICAgICAgICAgICBcImZpbGVfc2l6ZVwiOiAxNzgxMTksICAgICAgICAgICAgXCJjaWRcIjogXCJRbWQ1TmZ0d1VHajlZaGY1aFZXVGhpaUhkN3FpZnBFYVp1UVRVWERMcGdZdHFnXCIgICAgICAgIH0gICAgXSwgICAgXCJ0eF9yZXN1bHRcIjoge319IiwibWV0YWluZm8iOiIiLCJkYXRhaWQiOiIiLCJyb3dzIjpbeyJjb250ZW50c19maWxlX2lkIjoiMTQ4OCIsImNvbnRlbnRzX2lkIjoiODIwIiwidXNlcmlkIjoiOWJjZWNkOTA4NWZhZThmYTc4N2FjM2YzYmQzYzJmMjVhOTBlMDYxMCIsImZvbGRlciI6IkU6XFxjb250ZW50c1xcMjAxOTA5MDQiLCJmaWxlbmFtZSI6IjE1Njc3Nzk0MzkuanBnIiwicmVhbHNpemUiOiIxNzgxMTkiLCJzaXplIjoiMTc4MTE5Iiwic3RhdGUiOiJQIiwid2RhdGUiOiIxNTkyODk4NzYzIiwic29ydCI6IjEifV19LCJpbWdfcm93cyI6W3siaW1nIjoiYmFzaWNNZXRhXC8xNTY3NzMwNDk1LmpwZyIsImltZ19zaXplIjo5NjI0LCJjaWQiOiJRbU5nYXhYdGJQUm1zWFdXN0I4Rk55NWlXZ3MzeEVIajcyTDdFZjhCeHFjNnhhIn0seyJpbWciOiJiYXNpY01ldGFcLzE1Njc3ODI4NzguanBnIiwiaW1nX3NpemUiOjE3ODExOSwiY2lkIjoiUW1kNU5mdHdVR2o5WWhmNWhWV1RoaWlIZDdxaWZwRWFadVFUVVhETHBnWXRxZyJ9LHsiaW1nIjoiYmFzaWNNZXRhXC8xNTY3ODM2Mzk2LmpwZWciLCJpbWdfc2l6ZSI6MTIxNDM5MCwiY2lkIjoiUW1mVlFmalYyNGdGcnN6WHBzN2lBclIxWHpBSHhzNVNiREdjem5kSDZkZjlYOCJ9LHsiaW1nIjoiY29udGVudHNcLzE1Njc3Nzk0MzkuanBnIiwiaW1nX3NpemUiOjE3ODExOSwiY2lkIjoiUW1kNU5mdHdVR2o5WWhmNWhWV1RoaWlIZDdxaWZwRWFadVFUVVhETHBnWXRxZyJ9XX19'}
FEE=${5:-"100"}
FILE_HASHES=${6:-'"QmWDciaraGsMFneTFM67kKBUphWDpLLuqRreUM4exJqGGG"'}
CHUNKS=${7:-'37'}

./forTest-Login-Contents-Provider.sh
set -x
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"cid":"'${CID}'", "ccid":"'${CCID}'", "version":"'${VERSION}'", "info":"'${INFO}'", "fee":"'${FEE}'", "fileHasheLists":['${FILE_HASHES}'], "chunkLists":['${CHUNKS}']}' \
  https://localhost:55443/register/data
set +x
