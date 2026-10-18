import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Host del emulador Android hacia el equipo local. E0.5 consumirá /api/v1/health.
const apiBaseUrl = String.fromEnvironment(
  'API_BASE_URL',
  defaultValue: 'http://10.0.2.2:8080',
);

final dioProvider = Provider<Dio>((ref) {
  return Dio(BaseOptions(baseUrl: apiBaseUrl));
});
