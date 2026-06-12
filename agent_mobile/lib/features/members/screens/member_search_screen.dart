import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/api/dio_client.dart';
import '../../../core/models/member.dart';
import 'package:shimmer/shimmer.dart';

class MemberSearchScreen extends StatefulWidget {
  const MemberSearchScreen({super.key});

  @override
  State<MemberSearchScreen> createState() => _MemberSearchScreenState();
}

class _MemberSearchScreenState extends State<MemberSearchScreen> {
  final DioClient _dioClient = DioClient();
  final TextEditingController _searchController = TextEditingController();
  List<Member> _members = [];
  bool _isLoading = false;
  bool _hasSearched = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _searchController.addListener(_onSearchChanged);
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _onSearchChanged() {
    if (_searchController.text.trim().length >= 2) {
      _searchMembers();
    } else {
      setState(() {
        _members = [];
        _hasSearched = false;
      });
    }
  }

  Future<void> _searchMembers() async {
    setState(() {
      _isLoading = true;
      _hasSearched = true;
      _error = null;
    });

    try {
      final response = await _dioClient.dio.get(
        '/members/search',
        queryParameters: {'query': _searchController.text.trim()},
      );

      setState(() {
        _members = (response.data['data'] as List<dynamic>)
            .map((json) => Member.fromJson(json))
            .toList();
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Rechercher un membre'),
      ),
      body: SafeArea(
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.all(16),
              child: SearchBar(
                controller: _searchController,
                hintText: 'Nom, prénom ou téléphone',
                leading: const Icon(Icons.search),
                trailing: [
                  if (_searchController.text.isNotEmpty)
                    IconButton(
                      icon: const Icon(Icons.clear),
                      onPressed: () {
                        _searchController.clear();
                      },
                    ),
                ],
                elevation: WidgetStateProperty.all(1),
                padding:
                    WidgetStateProperty.all(const EdgeInsets.symmetric(horizontal: 16)),
              ),
            ),
            Expanded(
              child: _buildBody(),
            ),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push('/members/add'),
        icon: const Icon(Icons.person_add),
        label: const Text('Nouveau membre'),
      ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: 1,
        onTap: (index) {
          switch (index) {
            case 0:
              context.go('/');
              break;
            case 1:
              context.go('/members/search');
              break;
            case 2:
              context.go('/contributions');
              break;
          }
        },
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.dashboard),
            label: 'Accueil',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.people),
            label: 'Membres',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.receipt_long),
            label: 'Cotisations',
          ),
        ],
      ),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return _buildLoading();
    }

    if (_error != null) {
      return _buildError();
    }

    if (!_hasSearched) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(48),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.search, size: 64, color: AppColors.gray300),
              SizedBox(height: 16),
              Text(
                'Entrez au moins 2 caractères pour rechercher',
                textAlign: TextAlign.center,
                style: TextStyle(color: AppColors.gray500),
              ),
            ],
          ),
        ),
      );
    }

    if (_members.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(48),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.person_off_outlined, size: 64, color: AppColors.gray300),
              SizedBox(height: 16),
              Text(
                'Aucun membre trouvé',
                style: TextStyle(color: AppColors.gray500),
              ),
            ],
          ),
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: _members.length,
      itemBuilder: (context, index) {
        final member = _members[index];
        return Card(
          margin: const EdgeInsets.only(bottom: 8),
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor: AppColors.primaryLight,
              child: Text(
                member.displayName.substring(0, 1).toUpperCase(),
                style: const TextStyle(
                  color: AppColors.primary,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
            title: Text(member.displayName),
            subtitle: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(member.phone),
                if (member.memberCode != null)
                  Text(
                    member.memberCode!,
                    style: const TextStyle(
                      fontSize: 12,
                      color: AppColors.gray500,
                    ),
                  ),
              ],
            ),
            trailing: const Icon(Icons.chevron_right),
            onTap: () => context.push('/members/${member.id}'),
          ),
        );
      },
    );
  }

  Widget _buildLoading() {
    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: 5,
      itemBuilder: (context, index) {
        return Shimmer.fromColors(
          baseColor: AppColors.gray200,
          highlightColor: AppColors.gray100,
          child: Card(
            margin: const EdgeInsets.only(bottom: 8),
            child: const ListTile(),
          ),
        );
      },
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(48),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, size: 64, color: AppColors.danger),
            const SizedBox(height: 16),
            Text(
              _error!,
              textAlign: TextAlign.center,
              style: const TextStyle(color: AppColors.gray600),
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: _searchMembers,
              child: const Text('Réessayer'),
            ),
          ],
        ),
      ),
    );
  }
}
